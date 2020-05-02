var endless = {
    latestId: document.getElementById("all-profile-content").getAttribute('data-latest-id'),
    hasMore: true, // not at the end, has more contents
    proceed: true, // load the next page?

    load: function (fromId) {
        if (endless.proceed && endless.hasMore) {
            // Prvent user from loading too much contents suddenly
            // Block the loading until this one is done
            endless.proceed = false;

            // Load the next page
            var data = new FormData(),
                loading = document.getElementById("page-loading"),
                container = document.getElementById("all-profile-content");

            // Show loading message or spinner
            loading.style.display = "block";

            // AJAX request
            var xhr = new XMLHttpRequest();
            xhr.open('GET', "/api/v1/profile/index?fromId=" + fromId, true);
            xhr.onload = function () {
                var rsp = JSON.parse(this.response);
                // No more contents to load
                if (rsp.length === 0) {
                    loading.style.display = "none";
                    endless.hasMore = false;
                }

                // Contents loaded
                else {
                    rsp.forEach(function (item, index) {
                        // Append into container
                        var wrapper = document.createElement('div');
                        wrapper.className = "p-2 border border-light bg-white";
                        wrapper.style.overflow = "hidden";
                        var wrapperRow = document.createElement('div');
                        wrapperRow.className = "row";
                        var wrapperLeftCol = document.createElement('div');
                        wrapperLeftCol.className = "col";
                        var wrapperRightCol = document.createElement('div');
                        wrapperRightCol.className = "col text-right";

                        var elHp = document.createElement('div');
                        var a = document.createElement('a');
                        var linkText = document.createTextNode(item.homepage);
                        a.appendChild(linkText);
                        a.href = "/profile/" + item.id;
                        elHp.appendChild(a);
                        var elDescription = document.createElement('div');
                        var span = document.createElement('span');
                        span.className = "text-muted small";
                        var descriptionText = document.createTextNode(item.description ? item.description : (item.title ? item.title : 'no description'));
                        span.appendChild(descriptionText);
                        var reactions = item.reactions;
                        if (reactions) {
                            for (let reactionName in reactions) {
                                let elReactionBtn = document.createElement('button');
                                elReactionBtn.setAttribute('type', 'button');
                                elReactionBtn.classList.add('my-1', 'ml-1', 'btn', 'btn-light', 'reaction');
                                elReactionBtn.setAttribute('data-reaction', reactionName);
                                elReactionBtn.setAttribute('data-profile', item.id);
                                let elSpanCount = document.createElement('span');
                                let elIcon = document.createElement('i');
                                elIcon.classList.add('emoji', 'small', reactionName);
                                elSpanCount.classList.add('count');
                                elSpanCount.append(document.createTextNode(reactions[reactionName]));
                                elReactionBtn.appendChild(elSpanCount);
                                elReactionBtn.appendChild(elIcon);
                                wrapperRightCol.appendChild(elReactionBtn);
                            }
                        }
                        elDescription.appendChild(span);
                        wrapperLeftCol.appendChild(elHp);
                        wrapperLeftCol.appendChild(elDescription);
                        wrapperRow.appendChild(wrapperLeftCol);
                        wrapperRow.appendChild(wrapperRightCol);
                        wrapper.appendChild(wrapperRow);

                        container.appendChild(wrapper);
                        container.setAttribute('data-latest-id', item.id);
                    });
                    // hide loading message
                    loading.style.display = "none";
                    // Set the current page, unblock loading
                    endless.latestId = container.getAttribute('data-latest-id');
                    endless.proceed = true;
                    listenReactionButtons();
                }
            };
            xhr.send(data);
        }
    },

    listen: function(){
        // crossbrowser solution
        var docHeight = document.body.offsetHeight;
        docHeight = docHeight == undefined ? window.document.documentElement.scrollHeight : docHeight;

        var winheight = window.innerHeight;
        winheight = winheight == undefined ? document.documentElement.clientHeight : winheight;

        var scrollpoint = window.scrollY;
        scrollpoint = scrollpoint == undefined ? window.document.documentElement.scrollTop : scrollpoint;

        if ((scrollpoint + winheight) >= docHeight) {
            endless.load(endless.latestId);
        }
    }
};

window.onload = function () {
    // Attach scroll listener
    window.addEventListener("scroll", endless.listen);

    // Initial load contents
    endless.load(endless.latestId);
};
