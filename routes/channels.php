<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal de vueltas por empresa
Broadcast::channel('empresa.{empresaId}.vueltas', function ($user, $empresaId) {
    return (int) $user->empresa_id === (int) $empresaId;
});
