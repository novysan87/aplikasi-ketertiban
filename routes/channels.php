<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('violations', function () {
    return auth()->check();
});
