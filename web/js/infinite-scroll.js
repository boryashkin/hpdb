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
                        let wrapper = createProfileRow(item);

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
