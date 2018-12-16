"use strict";

jQuery(document).ready(function($){
    autocomplete(document.getElementById("main-search"));
    document.getElementById("main-search-form").addEventListener("submit", function (e) {
        e.preventDefault();
        return false;
    });
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
            '/api/v1/index',
            {query: val},
            function (response) {
                for (var i in response) {
                    /*create a DIV element for each matching element:*/
                    //b = document.createElement("DIV");
                    c = document.createElement("A");
                    c.href = "/profile/" + i;
                    /*make the matching letters bold:*/
                    let word = response[i].substr(0, wordLenLimit);
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