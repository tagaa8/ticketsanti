function validatePassword() {
    var password = document.getElementById('password').value;
    if (password.length < 8 || !/\d/.test(password)) {
        alert('La contraseña debe tener al menos 8 caracteres y contener al menos un número.');
        return false;
    }
    return true;
}

document.getElementById('btn-pasados').addEventListener('click', function() {
    fetchTickets('pasados');
});

document.getElementById('btn-hoy').addEventListener('click', function() {
    fetchTickets('hoy');
});

document.getElementById('btn-proximos').addEventListener('click', function() {
    fetchTickets('proximos');
});

function fetchTickets(type) {
    fetch(`fetch_tickets.php?type=${type}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('tickets-container');
            container.classList.add('tickets-container'); // Añadir la clase al contenedor
            container.innerHTML = '';
            data.forEach(ticket => {
                const ticketElement = document.createElement('div');
                ticketElement.classList.add('ticket');
                ticketElement.innerHTML = `
                    <img src="src/img/gallery/full/logo.jpg" class="ticket-logo" alt="Logo">
                    <div class="ticket-separator"></div>
                    <p><strong>Evento:</strong> ${ticket.nombre_evento}</p>
                    <p><strong>Ubicación:</strong> ${ticket.ubicacion_estadio}</p>
                    <p><strong>Estadio:</strong> ${ticket.nombre_estadio}</p>
                    <p><strong>Fecha:</strong> ${ticket.fecha}</p>
                    <p><strong>Hora:</strong> ${ticket.hora}</p>
                    <p><strong>Comprado por:</strong> ${ticket.nombre_usuario} ${ticket.apellido_usuario}</p>
                    <p><strong>Zona:</strong> ${ticket.zona}</p>
                    <p><strong>Asiento:</strong> ${ticket.asiento}</p>
                    <img src="src/codigos_qr/${ticket.qrcode}" class="ticket-qrcode" alt="QR Code">
                    <p class="ticket-id">#${ticket.id_ticket}</p>
                `;
                container.appendChild(ticketElement);
            });
        });
}