/**
 * Enable Caps Lock warning for any password input field.
 * @param {string} inputId - ID of the password field
 * @param {string} warningId - ID of the warning element
 */
function enableCapsLockWarning(inputId, warningId) {
    const input = document.getElementById(inputId);
    const warning = document.getElementById(warningId);

    if (!input || !warning) return;

    input.addEventListener("keyup", (event) => {
        if (event.getModifierState("CapsLock")) {
            warning.style.display = "block";
        } else {
            warning.style.display = "none";
        }
    });

    input.addEventListener("blur", () => {
        warning.style.display = "none";
    });
}
