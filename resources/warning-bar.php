<?php 
    $baneado = false;
    if (isset($_SESSION["cuenta_id"])) {
        $ban_data = esta_baneado($conn, $_SESSION["cuenta_id"]);
        if ($ban_data !== null) {
            $baneado = true;
            $expira_texto = $ban_data["expira"] === null
                ? "Suspensión permanente"
                : "Expira el " . (new DateTime($ban_data["expira"]))->format("d/m/Y \\a \\l\\a\\s H:i");
        }
    }
    if($baneado):  ?>
<div class="warning-bar">
    <svg class="warning-bar-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 3.5 22 20.5H2Z"/>
        <path d="M12 9.5v5"/>
        <path d="M12 17.7v.1"/>
    </svg>
    <div class="warning-bar-contenido">
        <div class="warning-bar-fila-superior">
            <span class="warning-bar-titulo">Cuenta suspendida</span>
            <span class="warning-bar-expira"><?= e($expira_texto) ?></span>
        </div>
        <p class="warning-bar-motivo">Motivo: <b><?= e($ban_data["motivo"]) ?></b></p>
    </div>
</div>

<?php endif; ?>