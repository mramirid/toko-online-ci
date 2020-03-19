<?php

if ($status == 'waiting') {
    $badge_status   = 'badge-primary';
    $status         = 'Menunggu Pembayaran';
}

if ($status == 'paid') {
    $badge_status   = 'badge-secondary';
    $status         = 'Terbayar';
}

if ($status == 'delivered') {
    $badge_status   = 'badge-success';
    $status         = 'Dikirim';
}

if ($status == 'cancel') {
    $badge_status   = 'badge-danger';
    $status         = 'Dibatalkan';
}

if ($status) : ?>
    <span class="badge badge-pill <?= $badge_status ?>"><?= $status ?></span>
<?php endif ?>
