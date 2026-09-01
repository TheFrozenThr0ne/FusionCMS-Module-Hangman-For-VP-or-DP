(function () {
    "use strict";

    var form = document.getElementById("hangman-word-form");

    if (!form) {
        return;
    }

    var idField = document.getElementById("hangman-word-id");
    var textField = document.getElementById("hangman-word-text");
    var difficultyField = document.getElementById("hangman-word-difficulty");
    var localeField = document.getElementById("hangman-word-locale");
    var title = document.getElementById("hangman-form-title");
    var titleText = title ? title.textContent : "";

    document.querySelectorAll(".hangman-edit").forEach(function (button) {
        button.addEventListener("click", function () {
            idField.value = button.getAttribute("data-id");
            textField.value = button.getAttribute("data-text");
            difficultyField.value = button.getAttribute("data-difficulty");
            localeField.value = button.getAttribute("data-locale");
            textField.focus();
        });
    });

    var reset = document.getElementById("hangman-word-reset");
    if (reset) {
        reset.addEventListener("click", function () {
            idField.value = "";
            form.reset();
            if (title) {
                title.textContent = titleText;
            }
        });
    }

    document.querySelectorAll(".hangman-delete").forEach(function (link) {
        link.addEventListener("click", function (event) {
            if (!window.confirm("?")) {
                event.preventDefault();
            }
        });
    });
})();