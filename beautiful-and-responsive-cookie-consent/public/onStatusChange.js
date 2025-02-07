var dataToPushToDl = getupdatedValues(cookieTypes, cookieRootName);
dataToPushToDl.event = "beautiful_cookie_consent_updated";

// goal: remove dismiss completly from application. Problem: is saved in cookie for "just info". For backward compatibility hard to change.
// first step: in datalayer dismiss will never appear.
if (status === "dismiss") {
  status = "allow";
}

dataToPushToDl.cookieconsent_status = status;
dataToPushToDl.statusBefore = chosenBefore;

window[dataLayerName] = window[dataLayerName] || [];
window[dataLayerName].push(dataToPushToDl);

function getupdatedValues(cookieTypes, cookieRootName) {
  var updatedValues = {};
  for (var i = 0, len = cookieTypes.length; i < len; i += 1) {
    var b = document.cookie.match(
      "(^|;)\\s*" + cookieRootName + "_" + cookieTypes[i]["cookie_suffix"] + "\\s*=\\s*([^;]+)"
    );
    var cookieValue = b ? b.pop() : "";
    if (cookieValue) {
      updatedValues["cookieconsent_status_" + cookieTypes[i]["cookie_suffix"]] = cookieValue;
    }
  }
  return updatedValues;
}
