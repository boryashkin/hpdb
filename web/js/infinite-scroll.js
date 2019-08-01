var endless = {
    page: 1, // "current page",
    hasMore: true, // not at the end, has more contents
    proceed: true, // load the next page?

    load: function (page) {
        if (endless.proceed && endless.hasMore) {
            // Prvent user from loading too much contents suddenly
            // Block the loading until this one is done
            endless.proceed = false;

            // Load the next page
            var data = new FormData(),
                nextPg = endless.page + 1,
                loading = document.getElementById("page-loading");
            data.append('page', nextPg);

            // Show loading message or spinner
            loading.style.display = "block";

            // AJAX request
            var xhr = new XMLHttpRequest();
            xhr.open('GET', "/api/v1/profile/index?page=" + page, true);
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
                        var wrapperCol = document.createElement('div');
                        wrapperCol.className = "col";

                        var elHp = document.createElement('div');
                        var a = document.createElement('a');
                        var linkText = document.createTextNode(item.homepage);
                        a.appendChild(linkText);
                        a.href = "/profile/" + item.profile_id;
                        elHp.appendChild(a);
                        var elDescription = document.createElement('div');
                        var span = document.createElement('span');
                        span.className = "text-muted small";
                        var descriptionText = document.createTextNode(item.hasOwnProperty('description') && item.description ? item.description : 'no description');
                        span.appendChild(descriptionText);
                        elDescription.appendChild(span);
                        wrapperCol.appendChild(elHp);
                        wrapperCol.appendChild(elDescription);
                        wrapperRow.appendChild(wrapperCol);
                        wrapper.appendChild(wrapperRow);

                        document.getElementById("all-profile-content").appendChild(wrapper);
                    });
                    // hide loading message
                    loading.style.display = "none";
                    // Set the current page, unblock loading
                    endless.page = nextPg;
                    endless.proceed = true;
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
            endless.load(endless.page);
        }
    }
};

window.onload = function () {
    // Attach scroll listener
    window.addEventListener("scroll", endless.listen);

    // Initial load contents
    endless.load(endless.page);
};
