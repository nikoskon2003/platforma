<?php
    header('Content-Type: application/javascript');
    include 'includes/config.php';
    $name = $siteName;
?>//<script>
self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    const sendNotification = (body, url) => {
        const title = "<?= $name; ?>";

        var options = {
            body: body,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
			data: {
				url: url
			},
            vibrate: [100, 50, 100, 50, 300]
        };

        return self.registration.showNotification(title, options);
    };

    if (event.data) {
        let message = event.data.text();
		
		let url = "";
        if(message.charAt(0) == 'o'){
            let parts = message.split('^*');
            if(parts.length >= 3 && parts[0] == 'o'){
                url = parts[1];
                parts.splice(0, 2);
                message = parts.join('^*');
            }
        }
		
        event.waitUntil(sendNotification(message, url));
    }
});

self.addEventListener('notificationclick', function(e) {
    clients.openWindow('/' + e.notification.data.url);
    e.notification.close();
});