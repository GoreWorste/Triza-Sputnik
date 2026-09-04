#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Generate MODX element files (chunks/templates/snippets) from extracted skeleton."""
import os, re, io

ROOT = "/home/gore/tizira"
SH = f"{ROOT}/shots/orig-contacts.html"
HOME = f"{ROOT}/shots/orig-home.html"
OUT = f"{ROOT}/modxbuild/elements"
os.makedirs(f"{OUT}/chunks", exist_ok=True)
os.makedirs(f"{OUT}/templates", exist_ok=True)
os.makedirs(f"{OUT}/snippets", exist_ok=True)

def rd(p):
    return io.open(p, encoding="utf-8").read()

def wr(p, s):
    io.open(p, "w", encoding="utf-8").write(s)
    print("wrote", p, len(s))

contacts = rd(SH)
home = rd(HOME)
lines = contacts.split("\n")
def L(a, b):
    return "\n".join(lines[a-1:b])

# ---------- HEAD ----------
# Extract head inner (between <head> and </head>) from contacts
head_inner = contacts.split("<head>",1)[1].split("</head>",1)[0]
# remove <base ...>
head_inner = re.sub(r'<base[^>]*>\s*', '', head_inner)
# remove og:* localhost + title (title handled in template)
head_inner = re.sub(r'<title>.*?</title>\s*', '', head_inner, flags=re.S)
head_inner = re.sub(r'<meta property="og:[^>]*>\s*', '', head_inner)
# replace localhost references
head_inner = head_inner.replace('http://localhost:8123','https://modx.romanovivv.ru')
head = "<title>[[*longtitle:notempty=`[[*longtitle]]`:default=`[[*pagetitle]]`]] - Кадровое агентство ТРИЗА-Спутник</title>\n" + head_inner.strip() + "\n"
wr(f"{OUT}/chunks/head.chunk.html", head)

def grab(html, pattern):
    m = re.search(pattern, html, re.S)
    if not m:
        raise SystemExit("NOT FOUND: " + pattern)
    return m.group(0)

# ---------- TOPBAR + LOGO + PHONES ----------
# from <section id="sp-top-bar"> up to (not including) <header id="sp-header">
seg = contacts.split('<section id="sp-top-bar">',1)[1]
seg = '<section id="sp-top-bar">' + seg.split('<header id="sp-header"')[0]
topbar = seg.replace('http://localhost:8123','')
wr(f"{OUT}/chunks/topbarLogo.chunk.html", topbar)

# ---------- MAP ----------
mapseg = grab(contacts, r'<section id="sp-full-width">.*?</section>')
wr(f"{OUT}/chunks/mapBlock.chunk.html", mapseg)

# ---------- FOOTER ----------
foot = grab(contacts, r'<footer id="sp-footer">.*?</footer>')
# fix policy/agreement links to local
foot = foot.replace("https://jobcv.ru/politika-obrabotki-personalnykh-dannykh-polzovatelej-sajta","/politika-obrabotki-personalnykh-dannykh-polzovatelej-sajta")
foot = foot.replace("https://jobcv.ru/soglashenie-ob-okazanii-uslug-po-ispolzovaniyu-sajta","/soglashenie-ob-okazanii-uslug-po-ispolzovaniyu-sajta")
wr(f"{OUT}/chunks/footer.chunk.html", foot)

# copyright
copyright = ('<section id="sp-copyright"><div class="container"><div class="row"><div id="sp-copyright1" '
 'class="col-sm-12 col-md-12"><div class="sp-column "><span class="sp-copyright"><center>'
 'СПУТНИК-Персонал © <script>document.write(new Date().getFullYear())</script>. Все права защищены.'
 '</center></span></div></div></div></div></section>')
wr(f"{OUT}/chunks/copyright.chunk.html", copyright)

# ---------- OFFCANVAS + TAIL (cookie/metrika/scrollup) ----------
tail = contacts.split('<!-- Off Canvas Menu -->',1)[1]
tail = tail.split('</body>')[0]
tail = tail.replace('http://localhost:8123','')
wr(f"{OUT}/chunks/tail.chunk.html", "<!-- Off Canvas Menu -->" + tail)

# ---------- SLIDER (home) ----------
m = re.search(r'<section id="sp-page-title">.*?</section>', home, re.S)
slider = m.group(0)
slider = slider.replace('http://localhost:8123','')
wr(f"{OUT}/chunks/slider.chunk.html", slider)

# ---------- SEARCH BAR (home) ----------
m = re.search(r'<section id="sp-search">.*?</section>', home, re.S)
searchbar = m.group(0).replace('http://localhost:8123','')
# make form action point to /vacancy and method get
searchbar = searchbar.replace('action=" /component/cwhire/?view=joblistings"','action="/vacancy"')
searchbar = searchbar.replace('action="/component/cwhire/?view=joblistings"','action="/vacancy"')
searchbar = re.sub(r'method="post"','method="get"',searchbar)
wr(f"{OUT}/chunks/searchBar.chunk.html", searchbar)

print("DONE")
