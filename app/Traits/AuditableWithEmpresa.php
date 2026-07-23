<?php

namespace App\Traits;

use OwenIt\Auditing\Auditable as OwenItAuditable;

trait AuditableWithEmpresa
{
    use OwenItAuditable;

    /**
     * {@inheritdoc}
     */
    public function transformAudit(array $data): array
    {
        // Si el modelo tiene empresa_id, lo guardamos en la auditoría
        if (isset($this->empresa_id)) {
            $data['empresa_id'] = $this->empresa_id;
        } elseif (auth()->check()) {
            // Si no, intentamos sacarlo del usuario autenticado
            $data['empresa_id'] = auth()->user()->empresa_id;
        }

        return $data;
    }
}
