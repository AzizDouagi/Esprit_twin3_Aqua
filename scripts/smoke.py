#!/usr/bin/env python3
"""Parcours HTTP réel sur une base de démonstration seedée (ne pas lancer en production)."""
import argparse
import http.cookiejar
import json
import os
import re
import urllib.error
import urllib.parse
import urllib.request

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument("--base-url", default="http://127.0.0.1:8000")
args = parser.parse_args()
base = args.base_url.rstrip("/")
password = os.environ.get("AQUASECURE_DEMO_PASSWORD", "Password#2026")


def client():
    return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))


def request(opener, path, expected=200, payload=None, csrf=None, token=None, method=None):
    headers = {}
    data = None
    if payload is not None:
        data = json.dumps(payload).encode()
        headers.update({"Content-Type": "application/json", "Accept": "application/json"})
    if csrf:
        headers["X-CSRF-TOKEN"] = csrf
    if token:
        headers.update({"Authorization": "Bearer " + token, "Accept": "application/json"})
    req = urllib.request.Request(base + path, data=data, headers=headers, method=method)
    try:
        response = opener.open(req, timeout=60)
    except urllib.error.HTTPError as error:
        response = error
    body = response.read()
    assert response.code == expected, f"{method or ('POST' if payload is not None else 'GET')} {path}: {response.code}, attendu {expected}"
    print(f"{response.code} {method or ('POST' if payload is not None else 'GET')} {path}")
    return body


def login(email):
    opener = client()
    html = request(opener, "/login").decode()
    csrf = re.search(r'name="_token" value="([^"]+)"', html).group(1)
    request(opener, "/login", payload={"email": email, "password": password, "_token": csrf})
    html = request(opener, "/profil").decode()
    csrf = re.search(r'name="_token" value="([^"]+)"', html).group(1)
    return opener, csrf


guest = client()
for path in ["/", "/carte", "/infrastructures", "/login", "/up", "/health"]:
    request(guest, path)
request(guest, "/page-inexistante", expected=404)
request(guest, "/api/v1/infrastructures", expected=401)
user, csrf = login("user@aquasecure.test")
request(user, "/admin", expected=403)
request(user, "/declarer-un-incident")
classification = json.loads(request(user, "/ia/classer", payload={"titre": "Fuite", "description": "Petite fuite visible"}, csrf=csrf))
assert classification["data"]["type"] == "Fuite"
assert classification["meta"]["provider"] == "fake"
admin, csrf = login("admin@aquasecure.test")
for path in ["/admin", "/admin/incidents?gravite=Critique&tri=gravite&sens=desc", "/admin/infrastructures", "/admin/ia/chat", "/admin/ia/anomalies"]:
    request(admin, path)
token = json.loads(request(guest, "/api/v1/auth/token", payload={"email": "admin@aquasecure.test", "password": password, "device_name": "validation-http"}))["token"]
try:
    infra = json.loads(request(guest, "/api/v1/infrastructures?per_page=1", token=token))["data"][0]["id"]
    incident = json.loads(request(guest, "/api/v1/incidents?per_page=1", token=token))["data"][0]["id"]
    request(guest, f"/api/v1/infrastructures/{infra}/mesures?per_page=1", token=token)
    request(admin, f"/admin/ia/incidents/{incident}/resume", payload={}, csrf=csrf)
    request(admin, f"/admin/ia/infrastructures/{infra}/risque")
    request(admin, f"/admin/ia/infrastructures/{infra}/risque", payload={}, csrf=csrf)
    request(admin, f"/admin/ia/infrastructures/{infra}/rapport")
    pdf = request(admin, f"/admin/ia/infrastructures/{infra}/rapport?format=pdf")
    assert pdf.startswith(b"%PDF-")
    request(admin, "/admin/ia/chat", payload={"messages": [{"role": "user", "content": "Combien d’incidents ouverts ?"}]}, csrf=csrf)
finally:
    request(guest, "/api/v1/auth/token", token=token, method="DELETE")
request(guest, "/api/v1/me", token=token, expected=401)
print("Smoke HTTP complet : OK (jeton de validation révoqué).")
