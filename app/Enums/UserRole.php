<?php

namespace App\Enums;

enum UserRole: string
{
    case Fan = 'fan';
    case Artiste = 'artiste';
    case Organisateur = 'organisateur';
    case Vendeur = 'vendeur';
    case Moderateur = 'moderateur';
    case Admin = 'admin';

    /**
     * Rôles nécessitant KYC validé + 2FA OTP avant activation.
     *
     * @return list<self>
     */
    public static function monetizable(): array
    {
        return [self::Artiste, self::Organisateur, self::Vendeur];
    }

    public function isMonetizable(): bool
    {
        return in_array($this, self::monetizable(), true);
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $role) => $role->value, self::cases());
    }

    /** @return list<string> */
    public static function monetizableValues(): array
    {
        return array_map(fn (self $role) => $role->value, self::monetizable());
    }
}
