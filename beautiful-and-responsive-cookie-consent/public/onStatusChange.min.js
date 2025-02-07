var dataToPushToDl = getupdatedValues(cookieTypes, cookieRootName);
function getupdatedValues(o, s) {
  for (var t = {}, a = 0, e = o.length; a < e; a += 1) {
    var u = document.cookie.match("(^|;)\\s*" + s + "_" + o[a].cookie_suffix + "\\s*=\\s*([^;]+)"),
      d = u ? u.pop() : "";
    d && (t["cookieconsent_status_" + o[a].cookie_suffix] = d);
  }
  return t;
}
(dataToPushToDl.event = "beautiful_cookie_consent_updated"),
  "dismiss" === status && (status = "allow"),
  (dataToPushToDl.cookieconsent_status = status),
  (dataToPushToDl.statusBefore = chosenBefore),
  (window[dataLayerName] = window[dataLayerName] || []),
  window[dataLayerName].push(dataToPushToDl);
