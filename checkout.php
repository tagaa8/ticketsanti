<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Santiago Tickets</title>
    <meta name="description" content="Finaliza tu compra de boletos de forma segura">
    <link rel="stylesheet" href="build/css/app.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

<body>
    <?php
    session_start();
    include 'db_connect.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $error_message = '';
    $success_message = '';
    $tickets = [];
    $total = 0;
    $info_evento = null;
    
    // Validar parámetros de entrada
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id_zona_fila = (int)($_GET['id_zona_fila'] ?? 0);
        $cantidad = (int)($_GET['cantidad'] ?? 0);
        $id_fecha = (int)($_GET['id_fecha'] ?? 0);
        $id_evento = (int)($_GET['id_evento'] ?? 0);
        
        if ($id_zona_fila <= 0 || $cantidad <= 0 || $id_fecha <= 0) {
            header('Location: index.php');
            exit;
        }

        try {
            // Obtener información del evento
            $sql_evento = "SELECT e.nombre_evento, e.foto, es.nombre_estadio, f.fecha, f.hora
                          FROM Fecha f
                          JOIN Evento e ON f.id_evento = e.id_evento
                          JOIN Estadios es ON f.id_estadio = es.id_estadio
                          WHERE f.id_fecha = ?";
            $stmt_evento = $pdo->prepare($sql_evento);
            $stmt_evento->execute([$id_fecha]);
            $info_evento = $stmt_evento->fetch();
            
            // Obtener los detalles de los tickets seleccionados
            $sql_tickets = "SELECT ZF.zona, ZF.fila, T.precio, T.asiento
                           FROM Zonas_Filas ZF
                           JOIN Ticket T ON ZF.id_zona_fila = T.id_zona_fila
                           WHERE ZF.id_zona_fila = ? AND T.id_fecha = ? AND T.id_usuario = 1
                           LIMIT ?";
            $stmt_tickets = $pdo->prepare($sql_tickets);
            $stmt_tickets->execute([$id_zona_fila, $id_fecha, $cantidad]);
            $tickets = $stmt_tickets->fetchAll();

            if (empty($tickets)) {
                $error_message = 'Los tickets seleccionados ya no están disponibles.';
            } else {
                $total = array_sum(array_column($tickets, 'precio'));
            }
        } catch (PDOException $e) {
            $error_message = 'Error al cargar la información. Por favor, intenta nuevamente.';
            error_log("Error en checkout.php (GET): " . $e->getMessage());
        }
    }

    // Procesar compra
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_compra'])) {
        $id_zona_fila = (int)$_POST['id_zona_fila'];
        $cantidad = (int)$_POST['cantidad'];
        $id_fecha = (int)$_POST['id_fecha'];
        $id_evento = (int)$_POST['id_evento'];
        $nombre_tarjeta = trim($_POST['nombre_tarjeta'] ?? '');
        $numero_tarjeta = preg_replace('/\s+/', '', $_POST['numero_tarjeta'] ?? '');
        $expiry_mes = $_POST['expiry_mes'] ?? '';
        $expiry_ano = $_POST['expiry_ano'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        
        // Validaciones
        if (empty($nombre_tarjeta) || empty($numero_tarjeta) || empty($expiry_mes) || empty($expiry_ano) || empty($cvv)) {
            $error_message = 'Todos los campos de pago son obligatorios.';
        } elseif (!preg_match('/^\d{13,19}$/', $numero_tarjeta)) {
            $error_message = 'Número de tarjeta inválido.';
        } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
            $error_message = 'CVV inválido.';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Verificar disponibilidad nuevamente
                $sql_disponibles = "SELECT id_ticket FROM Ticket WHERE id_zona_fila = ? AND id_fecha = ? AND id_usuario = 1 LIMIT ?";
                $stmt_disponibles = $pdo->prepare($sql_disponibles);
                $stmt_disponibles->execute([$id_zona_fila, $id_fecha, $cantidad]);
                $tickets_disponibles = $stmt_disponibles->fetchAll();

                if (count($tickets_disponibles) < $cantidad) {
                    throw new Exception('No hay suficientes tickets disponibles.');
                }

                // Actualizar los tickets para el usuario
                foreach ($tickets_disponibles as $ticket) {
                    $sql_update = "UPDATE Ticket SET id_usuario = ?, fecha_compra = NOW() WHERE id_ticket = ?";
                    $stmt_update = $pdo->prepare($sql_update);
                    $stmt_update->execute([$user_id, $ticket['id_ticket']]);
                }
                
                // Registrar la transacción (simulada)
                $sql_transaccion = "INSERT INTO Transacciones (id_usuario, id_fecha, cantidad_tickets, total, metodo_pago, estado, fecha_transaccion) 
                                   VALUES (?, ?, ?, ?, 'tarjeta', 'completada', NOW())";
                $stmt_transaccion = $pdo->prepare($sql_transaccion);
                $stmt_transaccion->execute([$user_id, $id_fecha, $cantidad, $total]);

                $pdo->commit();
                $success_message = 'Compra realizada exitosamente. Redirigiendo...';
                header('Refresh: 3; URL=micuenta.php');
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error_message = $e->getMessage();
                error_log("Error en checkout.php (POST): " . $e->getMessage());
            }
        }
    }
    ?>

    <div class="checkout-container">
        <div class="checkout-layout">
            <div class="checkout-main">
                <?php if ($error_message && empty($tickets)): ?>
                    <div class="checkout-section">
                        <div class="error-message">
                            <h2>❌ Error</h2>
                            <p><?php echo htmlspecialchars($error_message); ?></p>
                            <a href="index.php" class="btn btn-primary">Volver al Inicio</a>
                        </div>
                    </div>
                <?php elseif (!empty($tickets)): ?>
                    
                    <!-- Información del evento -->
                    <?php if ($info_evento): ?>
                    <div class="checkout-section">
                        <div class="section-header">
                            <h2>Información del Evento</h2>
                            <span class="section-step">1</span>
                        </div>
                        <div class="evento-checkout-info">
                            <h3><?php echo htmlspecialchars($info_evento['nombre_evento']); ?></h3>
                            <div class="evento-detalles">
                                <span>📅 <?php echo date('d/m/Y', strtotime($info_evento['fecha'])); ?></span>
                                <span>🕐 <?php echo date('H:i', strtotime($info_evento['hora'])); ?> hrs</span>
                                <span>📍 <?php echo htmlspecialchars($info_evento['nombre_estadio']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Resumen de boletos -->
                    <div class="checkout-section">
                        <div class="section-header">
                            <h2>Boletos Seleccionados</h2>
                            <span class="section-step">2</span>
                        </div>
                        <div class="tickets-resumen">
                            <?php foreach ($tickets as $index => $ticket): ?>
                                <div class="ticket-resumen-item">
                                    <div class="ticket-numero">Boleto #<?php echo $index + 1; ?></div>
                                    <div class="ticket-detalles">
                                        <span><strong>Zona:</strong> <?php echo htmlspecialchars($ticket['zona']); ?></span>
                                        <span><strong>Fila:</strong> <?php echo htmlspecialchars($ticket['fila']); ?></span>
                                        <span><strong>Asiento:</strong> <?php echo htmlspecialchars($ticket['asiento']); ?></span>
                                    </div>
                                    <div class="ticket-precio">$<?php echo number_format($ticket['precio']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Información de pago -->
                    <div class="checkout-section">
                        <div class="section-header">
                            <h2>Información de Pago</h2>
                            <span class="section-step">3</span>
                        </div>
                        
                        <?php if ($error_message): ?>
                            <div class="form-group">
                                <div class="form-error">
                                    <span class="error-icon">⚠️</span>
                                    <?php echo htmlspecialchars($error_message); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success_message): ?>
                            <div class="form-group">
                                <div class="form-success">
                                    <span class="success-icon">✅</span>
                                    <?php echo htmlspecialchars($success_message); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$success_message): ?>
                        <form action="checkout.php" method="POST" id="checkoutForm">
                            <input type="hidden" name="id_zona_fila" value="<?php echo $id_zona_fila; ?>">
                            <input type="hidden" name="cantidad" value="<?php echo $cantidad; ?>">
                            <input type="hidden" name="id_fecha" value="<?php echo $id_fecha; ?>">
                            <input type="hidden" name="id_evento" value="<?php echo $id_evento; ?>">
                            
                            <div class="form-group">
                                <label for="nombre_tarjeta">Nombre en la Tarjeta <span class="required">*</span></label>
                                <input type="text" id="nombre_tarjeta" name="nombre_tarjeta" required 
                                       placeholder="Como aparece en su tarjeta"
                                       value="<?php echo htmlspecialchars($_POST['nombre_tarjeta'] ?? ''); ?>">
                            </div>
                            
                            <div class="card-input-group">
                                <div class="card-icons">
                                    <div class="card-icon visa"></div>
                                    <div class="card-icon mastercard"></div>
                                    <div class="card-icon amex"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="numero_tarjeta">Número de Tarjeta <span class="required">*</span></label>
                                    <input type="text" id="numero_tarjeta" name="numero_tarjeta" required 
                                           placeholder="1234 5678 9012 3456" maxlength="19"
                                           value="<?php echo htmlspecialchars($_POST['numero_tarjeta'] ?? ''); ?>">
                                </div>
                                
                                <div class="card-row">
                                    <div class="expiry-cvv">
                                        <div class="form-group">
                                            <label for="expiry_mes">Mes <span class="required">*</span></label>
                                            <select id="expiry_mes" name="expiry_mes" required>
                                                <option value="">MM</option>
                                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                                    <option value="<?php echo sprintf('%02d', $i); ?>" 
                                                            <?php echo ($_POST['expiry_mes'] ?? '') === sprintf('%02d', $i) ? 'selected' : ''; ?>>
                                                        <?php echo sprintf('%02d', $i); ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="expiry_ano">Año <span class="required">*</span></label>
                                            <select id="expiry_ano" name="expiry_ano" required>
                                                <option value="">AAAA</option>
                                                <?php for ($i = date('Y'); $i <= date('Y') + 15; $i++): ?>
                                                    <option value="<?php echo $i; ?>" 
                                                            <?php echo ($_POST['expiry_ano'] ?? '') === (string)$i ? 'selected' : ''; ?>>
                                                        <?php echo $i; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="cvv">CVV <span class="required">*</span></label>
                                            <input type="text" id="cvv" name="cvv" required 
                                                   placeholder="123" maxlength="4" pattern="\d{3,4}"
                                                   value="<?php echo htmlspecialchars($_POST['cvv'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="finalizar_compra" class="btn-primary">
                                    🛒 Finalizar Compra - $<?php echo number_format($total); ?>
                                </button>
                                <a href="fechaevento.php?id_evento=<?php echo $id_evento; ?>&id_fecha=<?php echo $id_fecha; ?>" 
                                   class="btn-secondary">Cancelar</a>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                    
                <?php endif; ?>
            </div>
            
            <!-- Sidebar con resumen -->
            <?php if (!empty($tickets)): ?>
            <div class="checkout-sidebar">
                <div class="order-summary">
                    <h3>Resumen del Pedido</h3>
                    
                    <div class="summary-item">
                        <span class="item-label">Boletos (<?php echo count($tickets); ?>)</span>
                        <span class="item-value">$<?php echo number_format(array_sum(array_column($tickets, 'precio'))); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="item-label">Comisión de servicio</span>
                        <span class="item-value">$0</span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="item-label">Total</span>
                        <span class="item-value">$<?php echo number_format($total); ?></span>
                    </div>
                    
                    <div class="payment-security">
                        <div class="security-icon">🔒</div>
                        <div class="security-text">
                            <strong>Pago 100% Seguro</strong>
                            <p>Tus datos están protegidos con encriptación SSL</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>Santiago Montero. Todos los derechos reservados.</p>
    </footer>
    <script src="build/js/app.js"></script>
    <script>
        // Formatear número de tarjeta
        document.getElementById('numero_tarjeta')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            if (formattedValue !== e.target.value) {
                e.target.value = formattedValue;
            }
        });
        
        // Validar solo números en CVV
        document.getElementById('cvv')?.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
        
        // Prevenir envío múltiple del formulario
        document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            submitBtn.textContent = 'Procesando...';
        });
    </script>
</body>

</html>