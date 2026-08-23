(function () {
    const container = document.getElementById("nav-bell");
    if (!container) return;

    const button = document.getElementById("nav-bell-button");
    const panel = document.getElementById("nav-bell-panel");
    const badge = document.getElementById("nav-bell-badge");
    const list = document.getElementById("nav-bell-list");
    const markAllButton = document.getElementById("nav-bell-mark-all");

    const POLL_INTERVAL = 30000;

    const icons = {
        ban: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M6.2 6.2l11.6 11.6"/></svg>',
        unban: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.3 2.3L16 9.5"/></svg>',
        post_banned: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.8 21 19.5H3z"/><path d="M12 9.8v4.6"/><path d="M12 17.2h.01"/></svg>',
        post_deleted: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h14"/><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/><path d="M6.5 7 7.3 19a1 1 0 0 0 1 1h7.4a1 1 0 0 0 1-1L17.5 7"/></svg>'
    };
    const defaultIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/></svg>';

    function formatDate(sqlDate) {
        const date = new Date(sqlDate.replace(" ", "T"));
        if (isNaN(date.getTime())) return "";
        const now = new Date();
        const diffMs = now - date;
        const diffMin = Math.floor(diffMs / 60000);

        if (diffMin < 1) return "justo ahora";
        if (diffMin < 60) return diffMin + "m";
        const diffHours = Math.floor(diffMin / 60);
        if (diffHours < 24) return diffHours + "h";
        const diffDays = Math.floor(diffHours / 24);
        if (diffDays < 7) return diffDays + "d";
        return date.toLocaleDateString("es-AR", { day: "2-digit", month: "2-digit" });
    }

    function render(notifications) {
        if (!notifications.length) {
            list.innerHTML = '<p class="nav-bell-empty">No tenés notificaciones.</p>';
            return;
        }

        list.innerHTML = "";
        notifications.forEach((notification) => {
            const item = document.createElement("div");
            item.className = "nav-bell-item nav-bell-" + notification.tipo + (notification.leido == 0 ? " nav-bell-unread" : "");
            item.dataset.id = notification.id;
            item.innerHTML =
                '<span class="nav-bell-item-icon">' + (icons[notification.tipo] || defaultIcon) + '</span>' +
                '<div class="nav-bell-item-content">' +
                    '<p class="nav-bell-item-text"></p>' +
                    '<span class="nav-bell-item-date">' + formatDate(notification.fecha_creacion) + '</span>' +
                '</div>';
            item.querySelector(".nav-bell-item-text").textContent = notification.texto;

            if (notification.leido == 0) {
                item.addEventListener("click", () => markOne(notification.id, item));
            }

            list.appendChild(item);
        });
    }

    function updateBadge(unread) {
        if (unread > 0) {
            badge.textContent = unread > 9 ? "9+" : unread;
            badge.hidden = false;
        }
        else {
            badge.hidden = true;
        }
    }

    function loadNotifications() {
        fetch("api/v1/notifications/list.php")
            .then((res) => res.json())
            .then((data) => {
                if (!data.ok) return;
                updateBadge(data.unread);
                render(data.notifications);
            })
            .catch(() => {});
    }

    function markOne(id, item) {
        item.classList.remove("nav-bell-unread");

        const body = new URLSearchParams();
        body.append("action", "one");
        body.append("id", id);

        fetch("api/v1/notifications/mark-read.php", { method: "POST", body: body })
            .then((res) => res.json())
            .then((data) => {
                if (data.ok) loadNotifications();
            })
            .catch(() => {});
    }

    function markAll() {
        const body = new URLSearchParams();
        body.append("action", "all");

        fetch("api/v1/notifications/mark-read.php", { method: "POST", body: body })
            .then((res) => res.json())
            .then((data) => {
                if (data.ok) loadNotifications();
            })
            .catch(() => {});
    }

    function openPanel() {
        panel.hidden = false;
        button.setAttribute("aria-expanded", "true");
    }

    function closePanel() {
        panel.hidden = true;
        button.setAttribute("aria-expanded", "false");
    }

    button.addEventListener("click", (e) => {
        e.stopPropagation();
        if (panel.hidden) {
            openPanel();
            loadNotifications();
        }
        else {
            closePanel();
        }
    });

    document.addEventListener("click", (e) => {
        if (!panel.hidden && !container.contains(e.target)) closePanel();
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !panel.hidden) closePanel();
    });

    markAllButton.addEventListener("click", markAll);

    loadNotifications();
    setInterval(loadNotifications, POLL_INTERVAL);
})();