<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine if the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id;
    }

    /**
     * Determine if the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id 
            && ($user->isCompanyAdmin() || $user->isSuperAdmin());
    }

    /**
     * Determine if the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id 
            && ($user->isCompanyAdmin() || $user->isSuperAdmin());
    }
}
