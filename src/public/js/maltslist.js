//
// utility functions
//

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: "smooth" });
}

function swalPopup(type, title, text) {
    Swal.fire({
        icon: type,
        title: "<strong>" + title + "</strong>",
        text: text,
        showConfirmButton: true,
        confirmButtonText: "Okay",
        backdrop: true,
    });
}

function swalPopupDelete(elementId) {
    Swal.fire({
        icon: "warning",
        title: "<strong>Delete entry?</strong>",
        text: "Are you sure you want to permanently delete this entry?",
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: "Okay",
        cancelButtonText: "Cancel",
        backdrop: true,
    }).then((result) => {
        if (result.isConfirmed) {
            const id = elementId.split("-")[2];
            const ajaxUrl = `/ajax-de.php?id=${id}`;
            htmx.ajax("GET", ajaxUrl, {
                target: "#lists-container",
            });
        }
    });
}

// https://stackoverflow.com/a/61511955
function waitForElement(selector) {
    return new Promise((resolve) => {
        if (document.querySelector(selector)) {
            return resolve(document.querySelector(selector));
        }

        const observer = new MutationObserver((mutations) => {
            if (document.querySelector(selector)) {
                resolve(document.querySelector(selector));
                observer.disconnect();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    });
}

//
// sort.js
//

var options = {
    valueNames: [
        "sort-nr",
        "sort-title",
        "sort-score",
        "sort-progress",
        "sort-type",
        "sort-rewatch",
        "sort-comment",
    ],
};
waitForElement("#list-container-watching").then((element) => {
    new List("list-container-watching", options);
});
waitForElement("#list-container-plantowatch").then((element) => {
    new List("list-container-plantowatch", options);
});
waitForElement("#list-container-completed").then((element) => {
    new List("list-container-completed", options);
});
waitForElement("#list-container-favorites").then((element) => {
    new List("list-container-favorites", options);
});

//
// htmx
//

function populateChangeEntryModal(elementId) {
    const id = elementId.split("-")[1];
    const ajaxUrl = `/ajax-ce.php?id=${id}`;

    htmx.ajax("GET", ajaxUrl, "#ce-form");
}

htmx.on("htmx:afterRequest", function (event) {
    console.debug("Running htmx:afterRequest");

    // Rerender list after POST /ajax-ce.php
    if (
        event.detail.target.id == "ce-form" &&
        event.detail.successful == true &&
        event.detail.requestConfig.verb == "post"
    ) {
        console.debug("Got POST /ajax-ce.php");
        console.debug("Running GET /ajax-gd.php");
        htmx.ajax("GET", "/ajax-gd.php", "#lists-container");
        console.debug("Ending htmx:afterRequest");
        return true;
    }

    // Rerender list after POST /ajax-ae.php
    if (
        event.detail.target.id == "ae-form" &&
        event.detail.successful == true &&
        event.detail.requestConfig.verb == "post"
    ) {
        console.debug("Got POST /ajax-ae.php");

        console.debug("Running GET /ajax-gd.php");
        htmx.ajax("GET", "/ajax-gd.php", "#lists-container");

        // close modal and scroll to list entry if a HTTP 418 error code isn't present
        if (event.detail.xhr.status !== 418) {
            const modalElement = document.getElementById("modal-ae");
            const modal = bootstrap.Modal.getInstance(modalElement);
            modal.hide();

            const id = event.detail.xhr.getResponseHeader("entry_index");
            waitForElement(`#list-entry-${id}`).then((element) => {
                const listEntry = document.getElementById(`list-entry-${id}`);
                // scroll to new entry
                listEntry.scrollIntoView();
            });
        } else {
            console.debug("Got HTTP 418 from POST /ajax-ae.php");
        }

        console.debug("Ending htmx:afterRequest");
        return true;
    }

    // Rerender list after GET /ajax-de.php
    if (
        event.detail.target.id == "lists-container" &&
        event.detail.successful == true &&
        event.detail.requestConfig.verb == "get" &&
        event.detail.pathInfo.requestPath.includes("/ajax-de.php")
    ) {
        setTimeout(() => {
            console.debug("Running GET /ajax-gd.php");
            htmx.ajax("GET", "/ajax-gd.php", "#lists-container");
        }, 2000);
    }

    console.debug("None of the conditions matched");
    console.debug("Ending htmx:afterRequest");
});
