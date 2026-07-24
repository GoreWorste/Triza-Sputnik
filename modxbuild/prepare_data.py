#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import json, os, re

ROOT = "/home/gore/tizira"
CONT = f"{ROOT}/build/content"
OUT = f"{ROOT}/modxbuild/data"
os.makedirs(OUT, exist_ok=True)

def load(p):
    return [json.loads(l) for l in open(p, encoding="utf-8") if l.strip()]

# ---- category names ----
CATS = {
 1:"Без категории",2:"Административный персонал",3:"Бухгалтерия, управленческий учет, финансы предприятия",
 4:"Высший менеджмент",5:"Домашний персонал",6:"Сервис",7:"Информационные технологии, интернет, телеком",
 8:"Искусство, развлечения, масс-медиа",9:"Консультирование",10:"Маркетинг, реклама, PR",
 11:"Медицина, фармацевтика",12:"Наука, образование",13:"Начало карьеры, студенты",
 14:"Продажи, работа с клиентами",15:"Производство, промышленность, рабочий персонал",
 16:"Спортивные клубы, фитнес, салоны красоты",17:"Строительство, недвижимость",
 18:"Транспорт, логистика, склады",19:"Туризм, гостиницы, рестораны",20:"Управление персоналом, тренинги",
 21:"Юристы",22:"Закупки",25:"Секретари, помощники руководителя, переводчики",
 27:"Архивные вакансии",28:"Инженерно-технические специальности, производство",
}
CONTRACT = {0:"Полный рабочий день",1:"Временный сотрудник",2:"Сменный график",3:"Вахта"}

def start_label(sd):
    if not sd:
        return "Ближайшее время"
    m = re.match(r"(\d{4})-(\d{2})-(\d{2})", str(sd))
    if not m:
        return "Ближайшее время"
    y, mo, d = int(m.group(1)), int(m.group(2)), int(m.group(3))
    if y <= 1970 or (y == 0):
        return "Ближайшее время"
    return f"{d:02d}.{mo:02d}.{y}"

# ---- vacancies ----
vacs = load(f"{CONT}/vacancies.jsonl")
out_v = []
for v in vacs:
    cid = int(v.get("jobcategory") or 0)
    contract = int(v.get("contract") or 0)
    out_v.append({
        "id": int(v["id"]),
        "category_id": cid,
        "category_name": CATS.get(cid, "Без категории"),
        "title": v.get("title") or "",
        "location": (v.get("location") or "").strip(),
        "contract": contract,
        "contract_label": CONTRACT.get(contract, "Полный рабочий день"),
        "start_label": start_label(v.get("start_date")),
        "salary_html": v.get("description") or "",
        "tasks_html": v.get("tasks") or "",
        "profile_html": v.get("profile") or "",
        "perspective_html": v.get("perspective") or "",
        "contactinfo_html": v.get("contactinfo") or "",
        "homepage": int(v.get("homepage") or 0),
        "state": int(v.get("state") or 0),
        "ordering": int(v.get("ordering") or 0),
        "created": v.get("created") or "",
    })
json.dump(out_v, open(f"{OUT}/vacancies.json", "w", encoding="utf-8"), ensure_ascii=False)
print("vacancies:", len(out_v), "active:", sum(1 for x in out_v if x["state"] == 1))

# ---- categories (published state, from original DB) ----
CAT_STATE = {2:1,27:0,1:0,3:1,4:1,5:1,22:1,28:1,7:1,8:0,9:0,10:1,11:1,12:0,13:1,
             14:1,15:1,25:1,6:1,16:1,17:1,18:1,19:0,20:1,21:1}
cats = [{"id": cid, "name": CATS[cid], "state": CAT_STATE.get(cid, 0)} for cid in CATS]
json.dump(cats, open(f"{OUT}/categories.json", "w", encoding="utf-8"), ensure_ascii=False)
print("categories:", len(cats), "published:", sum(1 for c in cats if c["state"]==1 and c["id"]>1))

# ---- articles ----
arts = {int(a["id"]): a for a in load(f"{CONT}/articles.jsonl")}
def body(a):
    intro = a.get("introtext") or ""
    full = a.get("fulltext") or ""
    return (intro + ("\n" + full if full.strip() else "")).strip()

# static pages: alias -> definition
pages = []
def page(alias, art_id, parent="", title_mode="breadcrumb", show_search=0, pagetitle=None, isfolder=0, menuindex=0):
    a = arts.get(art_id)
    pages.append({
        "alias": alias,
        "parent_alias": parent,
        "pagetitle": pagetitle or (a["title"] if a else alias),
        "content": body(a) if a else "",
        "title_mode": title_mode,
        "show_search": show_search,
        "isfolder": isfolder,
        "menuindex": menuindex,
        "template": "base",
    })

# Home (site start)
page("home", 13, title_mode="slider", show_search=1, pagetitle="Главная", isfolder=1, menuindex=0)
page("sertifikat-sto", 17, parent="home", pagetitle="Сертификат СТО", menuindex=0)
page("about", 12, pagetitle="О компании", menuindex=1)
page("contacts", 7, pagetitle="Контакты", menuindex=4)
page("zapros-na-podbor-personala", 15, pagetitle="Запрос на подбор персонала", menuindex=6)
page("politika-obrabotki-personalnykh-dannykh-polzovatelej-sajta", 28, pagetitle="Политика конфиденциальности", menuindex=7)
page("soglashenie-ob-okazanii-uslug-po-ispolzovaniyu-sajta", 29, pagetitle="Соглашение об использовании сайта", menuindex=8)

# Trainings container + children
pages.append({"alias":"trainings-and-webinars","parent_alias":"","pagetitle":"Тренинги и вебинары",
  "content":"","title_mode":"breadcrumb","show_search":0,"isfolder":1,"menuindex":2,"template":"base"})
page("podbor-personala-v-moskvepodbor-personala-v-moskve-treningi-i-vebinaryi", 8, parent="trainings-and-webinars", menuindex=0)
page("programma-avtorskogo-modulnogo-treninga-olgi-shevelevoj", 9, parent="trainings-and-webinars", menuindex=1)
page("seminaryi-i-treningovyie-programmyi-dlya-biznesa", 10, parent="trainings-and-webinars", menuindex=2)
page("treningi", 11, parent="trainings-and-webinars", menuindex=3)
page("meropriyatiya-po-tekhnicheskomu-auditu", 14, parent="trainings-and-webinars", menuindex=4)

# Vacancy container (dynamic)
pages.append({"alias":"vacancy","parent_alias":"","pagetitle":"Вакансии","content":"[[!jobList]]",
  "title_mode":"breadcrumb","show_search":0,"isfolder":1,"menuindex":3,"template":"base"})
# Blog container (dynamic)
pages.append({"alias":"blog","parent_alias":"","pagetitle":"Лента новостей","content":"[[!newsList]]",
  "title_mode":"breadcrumb","show_search":0,"isfolder":1,"menuindex":5,"template":"base"})

json.dump(pages, open(f"{OUT}/pages.json","w",encoding="utf-8"), ensure_ascii=False)
print("pages:", len(pages))

# ---- news (blog children): published articles in category 15 ----
news = []
mi = 0
for aid in sorted([a for a in arts if int(arts[a].get("catid") or 0)==15 and int(arts[a].get("state") or 0)==1], reverse=True):
    a = arts[aid]
    news.append({
        "alias": a["alias"],
        "pagetitle": a["title"],
        "content": body(a),
        "created": a.get("created") or "",
        "menuindex": mi,
    })
    mi += 1
json.dump(news, open(f"{OUT}/news.json","w",encoding="utf-8"), ensure_ascii=False)
print("news:", len(news), "->", [n["alias"] for n in news])
print("DONE")
