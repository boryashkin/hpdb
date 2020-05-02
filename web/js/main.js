"use strict";

jQuery(document).ready(function($){
    autocomplete(document.getElementById("main-search"));
    document.getElementById("main-search-form").addEventListener("submit", function (e) {
        e.preventDefault();
        return false;
    });

    var groupBtns = document.getElementsByClassName('link-group');
    var groupContainerWrapper = document.getElementById('top-group-container-wrapper');
    var groupContainer = document.getElementsByClassName('group-container').item(0);
    var feedLangBtns = document.getElementsByClassName('link-feed');
    var feedLangContainer = document.getElementsByClassName('feed-container').item(0);
    var reactOnGroup = function () {
        var element = this;
        if (this.classList.contains('active')) {
            return;
        }
        var groupId = this.getAttribute("data-slug");
        var limit = this.getAttribute("data-limit");
        var groupName = this.getAttribute("title");
        for (var i = 0; i < groupBtns.length; i++) {
            groupBtns.item(i).classList.remove('active');
        }
        this.classList.add('active');
        groupContainerWrapper.getElementsByClassName('website-group-title').item(0).textContent = groupName + ':';

        $.get(
            "/api/v1/profile/index",
            {group: groupId, limit: limit ? limit : 100},
            function (content) {
                if (content) {
                    Array.from(groupContainer.getElementsByClassName('top-group-website')).forEach(function (item) {
                        item.remove();
                    });
                    for (var i = 0; i < content.length; i++) {
                        let website = content[i];
                        let websiteDescription = website.description ? website.description.substr(0, 80) : 'No description yet';
                        console.log(website);
                        var websiteEl = "<div class=\"p-2 border border-light bg-white top-group-website\">\n" +
                            "                            <div class=\"row\">\n" +
                            "                                <div class=\"col\">\n" +
                            "                                    <div>\n" +
                            "                                        <a href=\"/profile/" + website.id + "\">" + website.homepage.replace('http://', '').replace('https://', '') + "</a>\n" +
                            "                                    </div>\n" +
                            "                                    <div>\n" +
                            "                                        <span class=\"text-muted small\">" + websiteDescription + "</span>\n" +
                            "                                    </div>\n" +
                            "                                </div>\n" +
                            "                            </div>\n" +
                            "                        </div>";
                        groupContainer.insertAdjacentHTML("beforeEnd", websiteEl);
                    }
                }
            }
        );
    };
    var reactOnFeedLang = function () {
        if (this.classList.contains('active')) {
            return;
        }
        var lang = this.getAttribute("data-slug");
        var limit = this.getAttribute("data-limit");
        for (var i = 0; i < feedLangBtns.length; i++) {
            feedLangBtns.item(i).classList.remove('active');
        }
        this.classList.add('active');

        $.get(
            "/api/v1/feed",
            {lang: lang, limit: limit ? limit : 100, preview: 1},
            function (content) {
                if (content) {
                    Array.from(feedLangContainer.getElementsByClassName('feed-item')).forEach(function (item) {
                        item.remove();
                    });
                    for (var i = 0; i < content.length; i++) {
                        let feedItem = content[i];
                        if (!feedItem.description) {
                            continue;
                        }
                        console.log(feedItem);
                        var feedItemEl = "<div class=\"p-2 border border-light bg-white feed-item\">\n" +
                            "                            <div class=\"row\">\n" +
                            "                                <div class=\"col col-9\">\n" +
                            "                                    <div class=\"embed-responsive\">\n" +
                            "<a href=\"" + feedItem.link + "\" class=\"text-dark\">" + feedItem.title + "</a>" +
                            "                                    </div>\n" +
                            "                                    <div class=\"embed-responsive\">\n" +
                            "                                        <span class=\"text-muted small\">" + feedItem.description + "</span>\n" +
                            "                                    </div>\n" +
                            "                                    <div class=\"text-muted small\">" +
                            "<a href=\"/profile/" + feedItem.website_id + "\">" + feedItem.host + "</a></div>" +
                            "                                </div>\n" +
                            "                                <div class=\"col col-3 text-right align-text-bottom\">\n" +
                            "                                    <div class=\"text-muted small\">" +
                            feedItem.date + "</div>" +
                            "                                </div>\n" +
                            "                            </div>\n" +
                            "                        </div>";
                        feedLangContainer.insertAdjacentHTML("beforeEnd", feedItemEl);
                    }
                }
            }
        );
    };

    listenReactionButtons();
    for (let i = 0; i < groupBtns.length; i++) {
        groupBtns[i].addEventListener('click', reactOnGroup, false);
    }
    for (let i = 0; i < feedLangBtns.length; i++) {
        feedLangBtns[i].addEventListener('click', reactOnFeedLang, false);
    }
});

function autocomplete(inp) {
    var currentFocus;
    const wordLenLimit = 30;
    /*execute a function when someone writes in the text field:*/
    inp.addEventListener("input", function(e) {
        var a, b, c, i, val = this.value;
        /*close any already open lists of autocompleted values*/
        closeAllLists();
        if (!val) { return false;}
        currentFocus = -1;
        /*create a DIV element that will contain the items (values):*/
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
        /*append the DIV element as a child of the autocomplete container:*/
        this.parentNode.appendChild(a);
        jQuery.get(
            '/api/v1/profile/index-light',
            {query: val},
            function (response) {
                for (var i in response) {
                    /*create a DIV element for each matching element:*/
                    //b = document.createElement("DIV");
                    c = document.createElement("A");
                    c.href = "/profile/" + response[i].id;
                    /*make the matching letters bold:*/
                    let word = response[i].homepage.substr(0, wordLenLimit);
                    let valPos = word.search(val);
                    c.innerHTML = "";
                    if (valPos !== -1) {
                        c.innerHTML += word.substr(0, valPos);
                        c.innerHTML += "<strong>" + word.substr(valPos, val.length) + "</strong>";
                        c.innerHTML += word.substr(valPos + val.length);
                    } else {
                        c.innerHTML += word.substr(0, wordLenLimit);
                    }
                    /*insert a input field that will hold the current array item's value:*/

                    /*execute a function when someone clicks on the item value (DIV element):*/
                    c.addEventListener("click", function(e) {
                        closeAllLists();
                    });
                    a.appendChild(c);
                }
            }
        );
    });
    /*execute a function presses a key on the keyboard:*/
    inp.addEventListener("keydown", function(e) {
        var x = document.getElementById(this.id + "autocomplete-list");
        if (x) x = x.getElementsByTagName("div");
        if (e.code == "ArrowDown") {
            /*If the arrow DOWN key is pressed,
            increase the currentFocus variable:*/
            currentFocus++;
            /*and and make the current item more visible:*/
            addActive(x);
        } else if (e.code == "ArrowUp") { //up
            /*If the arrow UP key is pressed,
            decrease the currentFocus variable:*/
            currentFocus--;
            /*and and make the current item more visible:*/
            addActive(x);
        } else if (e.code == "Enter") {
            /*If the ENTER key is pressed, prevent the form from being submitted,*/
            e.preventDefault();
            if (currentFocus > -1) {
                /*and simulate a click on the "active" item:*/
                if (x) x[currentFocus].click();
            }
        }
    });
    function addActive(x) {
        /*a function to classify an item as "active":*/
        if (!x) return false;
        /*start by removing the "active" class on all items:*/
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (x.length - 1);
        /*add class "autocomplete-active":*/
        x[currentFocus].classList.add("autocomplete-active");
    }
    function removeActive(x) {
        /*a function to remove the "active" class from all autocomplete items:*/
        for (var i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
        }
    }
    function closeAllLists(elmnt) {
        /*close all autocomplete lists in the document,
        except the one passed as an argument:*/
        var x = document.getElementsByClassName("autocomplete-items");
        for (var i = 0; i < x.length; i++) {
            if (elmnt != x[i] && elmnt != inp) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }

    /*execute a function when someone clicks in the document:*/
    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    });
}

let reactOnProfile = function () {
    var element = this;
    var reaction = this.getAttribute("data-reaction");
    var profileId = this.getAttribute("data-profile");
    var reactedClass = "reacted";
    var arr = element.className.split(" ");
    if (arr.indexOf(reactedClass) == -1) {
        element.className += " " + reactedClass;
    } else {
        return;
    }
    var cntEls = this.getElementsByClassName("count");
    var cntEl = cntEls[0] ? cntEls[0] : null;
    $.post(
        "/api/v1/reaction",
        {profile_id: profileId, reaction: reaction},
        function () {
            if (cntEl) {
                console.log(cntEl);
                cntEl.innerText++;
            }
        }
    );
};

function listenReactionButtons() {
    var reactionBtns = document.getElementsByClassName("reaction");
    for (var i = 0; i < reactionBtns.length; i++) {
        reactionBtns[i].addEventListener('click', reactOnProfile, false);
    }
}
