export default function notify(order) {
    if (!("Notification" in window)) {
        // Check if the browser supports notifications
        alert("This browser does not support desktop notification");
    } else if (Notification.permission === "granted") {
        // Check whether notification permissions have already been granted;
        // if so, create a notification
        const notification = new Notification("Nuevo Pedido", {
            body: 'Da click para ver los detalles del nuevo pedido #' + order.id,
            icon: 'http://localhost/img/logo.svg',
        });

        notification.onclick = (event) => {
            const {
                host
              } = window.location
            event.preventDefault(); // prevent the browser from focusing the Notification's tab
            window.open("/admin/order/"+order.id, "_blank");
        };
        // …
    } else if (Notification.permission !== "denied") {
        // We need to ask the user for permission
        Notification.requestPermission().then((permission) => {
            // If the user accepts, let's create a notification
            if (permission === "granted") {
                const notification = new Notification("Hi there!");
                // …
            }
        });
    }

}
