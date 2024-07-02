// keywords.js

document.addEventListener("DOMContentLoaded", function() {
  const keywordsInput = document.getElementById("keywordsInput");

  const keywordsContainer = document.getElementById("keywordsContainer");
  const tagsField = document.querySelector(".tags-input");

  const createForm = document.querySelector("#create_folder");

  function getCurrentKeywords() {
    return Array.from(keywordsContainer.querySelectorAll(".keyword"))
        .map((keyword) => keyword.textContent.replace("×", "").trim().toLowerCase());
}

function updateTagsField() {
    const tags = getCurrentKeywords();
    tagsField.value = tags.join(",");
}

  keywordsInput.addEventListener("keyup", function(event) {
    if (event.key === "Enter" || event.key === ",") {
      event.preventDefault();
      const keyword = keywordsInput.value.trim().replace(/,$/, "");

        if (keyword) {
          addKeyword(keyword);
          keywordsInput.value = "";
        }
    }

    if (event.key === "Backspace" && !keywordsInput.value) {
      const keywords = keywordsContainer.querySelectorAll(".keyword");
      if (keywords.length > 0) {
        const lastKeyword = keywords[keywords.length - 1];
        keywordsContainer.removeChild(lastKeyword);
        updateTagsField();
      }
    }
  });

  createForm.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
      event.preventDefault();
    }
  });

  keywordsContainer.addEventListener("click", function(event) {
    if (event.target.classList.contains("remove-keyword")) {
      const keyword = event.target.parentElement;
      keywordsContainer.removeChild(keyword);
    }
  });

  function addKeyword(keyword) {

    const currentKeywords = getCurrentKeywords();

    if (currentKeywords.includes(keyword.toLowerCase())) {
        return;
    }

    const keywordElement = document.createElement("div");
    keywordElement.className = "keyword";
    keywordElement.textContent = keyword;

    const removeIcon = document.createElement("span");
    removeIcon.className = "remove-keyword";
    removeIcon.innerHTML = "&times;";
    keywordElement.appendChild(removeIcon);
    removeIcon.addEventListener("click", () => {
      keywordsContainer.removeChild(keywordElement);
      updateTagsField();
    });

    keywordsContainer.insertBefore(keywordElement, keywordsInput);
    updateTagsField();
  }
});
