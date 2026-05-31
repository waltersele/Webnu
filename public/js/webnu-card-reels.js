(function () {
    var videos = document.querySelectorAll('.wn-card-reel__video[data-src]');
    if (!videos.length) {
        return;
    }

    var activeVideo = null;

    function pauseOthers(except) {
        videos.forEach(function (video) {
            if (video !== except && !video.paused) {
                video.pause();
            }
        });
    }

    function loadVideo(video) {
        var src = video.getAttribute('data-src');
        if (!src || video.getAttribute('src')) {
            return;
        }
        video.setAttribute('src', src);
        video.load();
    }

    function playVideo(video) {
        loadVideo(video);
        pauseOthers(video);
        activeVideo = video;
        var playPromise = video.play();
        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(function () {});
        }
    }

    function pauseVideo(video) {
        if (!video.paused) {
            video.pause();
        }
        if (activeVideo === video) {
            activeVideo = null;
        }
    }

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                var video = entry.target;
                if (entry.isIntersecting && entry.intersectionRatio >= 0.35) {
                    playVideo(video);
                } else {
                    pauseVideo(video);
                }
            });
        }, {
            root: null,
            rootMargin: '0px',
            threshold: [0, 0.35, 0.6]
        });

        videos.forEach(function (video) {
            observer.observe(video);
        });
    } else if (videos[0]) {
        playVideo(videos[0]);
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            videos.forEach(pauseVideo);
        }
    });
})();
