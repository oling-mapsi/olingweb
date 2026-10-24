"""Explicit loopback-only fixture recipe. Cookies/CSRF remain in memory, never logged."""
import http.client
import json
from datetime import datetime, timezone
from html.parser import HTMLParser
from http.cookies import SimpleCookie
from pathlib import Path
from urllib.parse import urlencode


class Fields(HTMLParser):
    def __init__(self, html):
        super().__init__()
        self.values = {}
        self.feed(html)

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == "input" and attrs.get("name"):
            self.values[attrs["name"]] = attrs.get("value", "")


class FixtureClient:
    def __init__(self):
        self.cookies = SimpleCookie()
        self.cookie_attributes = []

    def request(self, path, data=None, host="ai.localhost"):
        # Never resolve a supplied host or follow redirects to another destination.
        assert path.startswith("/") and host in {"ai.localhost", "crm.localhost", "127.0.0.1"}
        connection = http.client.HTTPConnection("127.0.0.1", 8094, timeout=10)
        headers = {"Host": host + ":8094"}
        if self.cookies:
            headers["Cookie"] = "; ".join(m.OutputString(attrs=[]) for m in self.cookies.values())
        if data is not None:
            headers["Content-Type"] = "application/x-www-form-urlencoded"
        connection.request("POST" if data is not None else "GET", path,
                           urlencode(data) if data is not None else None, headers)
        response = connection.getresponse()
        pairs = response.getheaders()
        body = response.read().decode("utf8")
        for name, value in pairs:
            if name.lower() == "set-cookie":
                cookie = SimpleCookie(value)
                for item in cookie.values():
                    if item.key == "OLINGAISESSID":
                        self.cookie_attributes.append({"name": item.key, "domain_absent": not item["domain"],
                                                       "http_only": bool(item["httponly"]), "same_site": item["samesite"]})
                self.cookies.update(cookie)
        result = response.status, dict((k.lower(), v) for k, v in pairs), body
        connection.close()
        return result

    def login(self, username):
        status, _, html = self.request("/ai/login")
        assert status == 200
        fields = Fields(html).values
        return self.request("/ai/login", {"_username": username, "_password": "Demo-local-only-42!",
                                           "_csrf_token": fields["_csrf_token"]})


checks = []


def check(name, condition):
    assert condition, name
    checks.append({"check": name, "passed": True})


admin = FixtureClient()
status, headers, _ = admin.request("/admin/ai")
check("anonymous_requires_login", status == 302 and headers.get("location", "").endswith("/ai/login"))
status, _, _ = admin.login("ai-demo")
check("fixture_admin_login", status == 302)
for view in ["home", "missions", "assets", "prompts"]:
    status, headers, _ = admin.request("/admin/ai?view=" + view)
    check("view_" + view, status == 200)
    check("private_headers_" + view, "no-store" in headers.get("cache-control", "") and "noindex" in headers.get("x-robots-tag", ""))
check("host_only_http_only_same_site_session", bool(admin.cookie_attributes) and all(
    item["domain_absent"] and item["http_only"] and item["same_site"].lower() == "lax"
    for item in admin.cookie_attributes))
status, _, _ = admin.request("/admin/ai/missions", {"_token": "SYNTHETIC_INVALID_CSRF", "title": "Must not create"})
check("invalid_csrf_create_refused", status == 403)
for host, path in [("127.0.0.1", "/admin/ai"), ("crm.localhost", "/ai/login"), ("ai.localhost", "/admin/crm")]:
    status, _, _ = admin.request(path, host=host)
    check("route_isolation_" + host + path, status == 404)
for host, path in [("crm.localhost", "/admin/crm"), ("127.0.0.1", "/admin")]:
    status, headers, _ = admin.request(path, host=host)
    check("ai_session_not_accepted_" + host, status == 302 and "login" in headers.get("location", ""))
status, _, _ = admin.request("/api/ai-relay", {})
check("relay_disabled", status == 403)
status, _, _ = admin.request("/api/ai/mcp")
check("tracking_not_connected", status == 503)
reader = FixtureClient()
status, _, _ = reader.login("ai-demo-reader")
check("fixture_reader_authenticates", status == 302)
status, _, _ = reader.request("/admin/ai")
check("non_admin_refused", status == 403)
status, _, html = admin.request("/admin/ai")
fields = Fields(html).values
status, _, _ = admin.request("/ai/logout", {"_csrf_token": fields["_csrf_token"]})
check("logout", status == 302)
status, _, _ = admin.request("/admin/ai")
check("logout_revokes_access", status == 302)
report = {"at": datetime.now(timezone.utc).isoformat(), "target": "127.0.0.1:8094",
          "fixtures_only": True, "real_codex_started": False, "checks": checks,
          "cookie_values_saved": False, "csrf_values_saved": False}
Path("docs/ai/local-http-result.json").write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n")
print(json.dumps({"passed": len(checks), "failed": 0, "report": "docs/ai/local-http-result.json"}))
