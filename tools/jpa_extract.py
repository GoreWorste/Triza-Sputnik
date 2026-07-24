#!/usr/bin/env python3
"""Minimal Akeeba JPA archive extractor."""
import os
import sys
import struct
import zlib

def read_exact(f, n):
    data = f.read(n)
    if len(data) != n:
        raise EOFError("Unexpected EOF")
    return data

def decompress(data, ctype):
    if ctype == 0:
        return data
    # gzip / zlib deflate
    for wbits in (15, -15, 47, 31):
        try:
            return zlib.decompress(data, wbits)
        except Exception:
            continue
    raise ValueError("Cannot decompress entity")

def main(archive, dest):
    os.makedirs(dest, exist_ok=True)
    with open(archive, "rb") as f:
        sig = read_exact(f, 3)
        if sig != b"JPA":
            raise ValueError("Not a JPA archive: %r" % sig)
        header_len = struct.unpack("<H", read_exact(f, 2))[0]
        major = read_exact(f, 1)[0]
        minor = read_exact(f, 1)[0]
        file_count = struct.unpack("<I", read_exact(f, 4))[0]
        uncompressed = struct.unpack("<I", read_exact(f, 4))[0]
        compressed = struct.unpack("<I", read_exact(f, 4))[0]
        print(f"JPA v{major}.{minor}, files={file_count}, uncompressed={uncompressed}, compressed={compressed}, header_len={header_len}")
        # seek to end of standard header
        f.seek(header_len, os.SEEK_SET)

        n = 0
        dirs = files = links = 0
        while True:
            block_start = f.tell()
            entsig = f.read(3)
            if len(entsig) < 3:
                break
            if entsig != b"JPF":
                print(f"Stop: bad entity signature {entsig!r} at {block_start}")
                break
            ent_header_len = struct.unpack("<H", read_exact(f, 2))[0]
            path_len = struct.unpack("<H", read_exact(f, 2))[0]
            path = read_exact(f, path_len).decode("utf-8", "surrogateescape")
            etype = read_exact(f, 1)[0]
            ctype = read_exact(f, 1)[0]
            comp_size = struct.unpack("<I", read_exact(f, 4))[0]
            uncomp_size = struct.unpack("<I", read_exact(f, 4))[0]
            perms = struct.unpack("<I", read_exact(f, 4))[0]
            # skip to data start (past any extra header fields)
            data_start = block_start + ent_header_len
            f.seek(data_start, os.SEEK_SET)

            out_path = os.path.join(dest, path)
            # prevent path traversal
            real_dest = os.path.realpath(dest)
            real_out = os.path.realpath(out_path)
            if not real_out.startswith(real_dest):
                print(f"Skip unsafe path: {path}")
                f.seek(comp_size, os.SEEK_CUR)
                continue

            if etype == 0:  # dir
                os.makedirs(out_path, exist_ok=True)
                dirs += 1
            elif etype == 2:  # symlink
                target = read_exact(f, comp_size)
                target = decompress(target, ctype).decode("utf-8", "surrogateescape")
                os.makedirs(os.path.dirname(out_path), exist_ok=True)
                try:
                    if os.path.lexists(out_path):
                        os.remove(out_path)
                    os.symlink(target, out_path)
                except OSError:
                    pass
                links += 1
            else:  # file
                os.makedirs(os.path.dirname(out_path), exist_ok=True)
                raw = read_exact(f, comp_size) if comp_size else b""
                out = decompress(raw, ctype) if comp_size else b""
                with open(out_path, "wb") as w:
                    w.write(out)
                files += 1
            n += 1
            if n % 1000 == 0:
                print(f"  {n} entities...")
        print(f"Done. {n} entities: {files} files, {dirs} dirs, {links} links")

if __name__ == "__main__":
    main(sys.argv[1], sys.argv[2])
