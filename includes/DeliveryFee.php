<?php
/**
 * SouthDev Home Depot – Davao City delivery fee by barangay zone.
 * Store base: Talomo / Juna Ave area.
 *
 * Near ₱300 | Mid ₱400 | Far ₱500
 * Free delivery when cart subtotal >= ₱1,000
 */

class DeliveryFee
{
    public const FEE_NEAR = 300.0;
    public const FEE_MID  = 400.0;
    public const FEE_FAR  = 500.0;
    public const FREE_THRESHOLD = 10000.0;

    /** @var array<string, string> barangay name => near|mid|far */
    private static $zones = null;

    public static function freeThreshold(): float
    {
        return self::FREE_THRESHOLD;
    }

    public static function zoneFees(): array
    {
        return [
            'near' => self::FEE_NEAR,
            'mid'  => self::FEE_MID,
            'far'  => self::FEE_FAR,
        ];
    }

    /**
     * @return array{fee: float, zone: string, free: bool, zone_fee: float}
     */
    public static function calculate(?string $barangay, float $subtotal): array
    {
        if ($subtotal >= self::FREE_THRESHOLD) {
            return [
                'fee'      => 0.0,
                'zone'     => self::resolveZone($barangay),
                'free'     => true,
                'zone_fee' => 0.0,
            ];
        }

        $zone = self::resolveZone($barangay);
        $zoneFee = self::feeForZone($zone);

        return [
            'fee'      => $zoneFee,
            'zone'     => $zone,
            'free'     => false,
            'zone_fee' => $zoneFee,
        ];
    }

    public static function resolveZone(?string $barangay): string
    {
        $name = self::normalizeBarangay($barangay);
        if ($name === '') {
            return 'mid';
        }

        $map = self::zoneMap();
        if (isset($map[$name])) {
            return $map[$name];
        }

        // Fuzzy: address may include "Brgy. X" or partial match
        foreach ($map as $key => $zone) {
            if ($name === $key || str_contains($name, $key) || str_contains($key, $name)) {
                return $zone;
            }
        }

        return 'mid';
    }

    public static function feeForZone(string $zone): float
    {
        return match ($zone) {
            'near' => self::FEE_NEAR,
            'far'  => self::FEE_FAR,
            default => self::FEE_MID,
        };
    }

    /**
     * Extract barangay from a combined shipping address like
     * "123 Street, Brgy. Talomo, Davao City"
     */
    public static function extractBarangayFromAddress(?string $address): ?string
    {
        if ($address === null || trim($address) === '') {
            return null;
        }

        if (preg_match('/Brgy\.?\s*([^,]+)/i', $address, $m)) {
            return trim($m[1]);
        }

        $map = self::zoneMap();
        $normalized = self::normalizeBarangay($address);
        foreach (array_keys($map) as $brgy) {
            if (str_contains($normalized, $brgy)) {
                return $brgy;
            }
        }

        return null;
    }

    /**
     * Payload for checkout.js live fee updates.
     */
    public static function clientConfig(float $subtotal): array
    {
        return [
            'subtotal'       => round($subtotal, 2),
            'free_threshold' => self::FREE_THRESHOLD,
            'fees'           => self::zoneFees(),
            'zones'          => self::zoneMap(),
        ];
    }

    private static function normalizeBarangay(?string $barangay): string
    {
        if ($barangay === null) {
            return '';
        }
        $name = strtolower(trim($barangay));
        $name = preg_replace('/^brgy\.?\s*/i', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return $name ?? '';
    }

    /**
     * @return array<string, string> lowercase barangay => zone
     */
    private static function zoneMap(): array
    {
        if (self::$zones !== null) {
            return self::$zones;
        }

        $near = [
            'Talomo', 'Matina Crossing', 'Matina Aplaya', 'Matina Pangi', 'Matina Biao',
            'Bangkal', 'Ulas', 'Catalunan Grande', 'Catalunan Pequeño', 'Dumoy',
            'Baliok', 'Magtuod', 'Langub', 'Bago Aplaya', 'Bago Gallera', 'Bucana',
            'Centro (Poblacion)', 'San Isidro (Bajada)', 'Kap. Tomas Monteverde Sr.',
            'Rafael Castillo', 'Gov. Vicente Duterte', 'Leon Garcia Sr.',
            'Gov. Paciano Bangoy', 'Ma-a', 'Pampanga', 'Sto. Niño', 'Lapu-Lapu',
            'Crossing Bayabas', 'Bago Oshiro',
        ];

        $far = [
            'Calinan', 'Marilog', 'Bunawan', 'Baguio', 'Malagos', 'Malabog',
            'Mapula', 'Pangyan', 'Paquibato', 'Paradise Embak', 'Salapawan',
            'Sumimao', 'Lumiad', 'Fatima (Benedicto)', 'Gumalang', 'Tamugan',
            'Wangan', 'Dalag', 'Datu Salumay', 'Marilog', 'Megkawayan',
            'Saloy', 'Talandang', 'Tamayong', 'Tawan-Tawan', 'Wines',
            'Camansi', 'Colosas', 'Dacudao', 'Eden', 'Gumitan', 'Kilate',
            'Lamanan', 'Malaguli', 'Mudiang', 'Mulig', 'Sibulan', 'Sirib',
            'Subasta', 'Tagluno', 'Talisay', 'Tibuloy', 'Tikalon', 'Tungakalan',
            'Alambre', 'Atan-Awe', 'Balengaeng', 'Bantol', 'Baracatan', 'Bato',
            'Bayabas', 'Biao Escuela', 'Biao Guianga', 'Biao Joaquin', 'Callawa',
            'Carmen', 'Catigan', 'Cawayan', 'Daliaon Plantation', 'Dominga',
            'Lasang', 'Mahayag', 'New Carmen', 'New Valencia', 'Saban',
            'Salmonan', 'San Antonio', 'San Rafael', 'Tagakpan',
        ];

        // Everything else in the Davao list = mid (Toril, Buhangin, Tugbok, etc.)
        $mid = [
            'Acacia', 'Agdao', 'Alejandro Navarro (Linoan)', 'Alfonso Angliongto Sr.',
            'Angalan', 'Baganihan', 'Bangkas Heights', 'Buhangin', 'Cabantian',
            'Communal', 'Daliao', 'Guadalupe', 'Ilang', 'Indangan', 'Lacson',
            'Lizada', 'Los Amigos', 'Lubogan', 'Mabuhay', 'Magsaysay', 'Mandug',
            'Marapangi', 'Mintal', 'Panacan', 'Riverside', 'Sasa', 'Sirawan',
            'Tacunan', 'Tibungco', 'Tigatto', 'Toril', 'Tugbok', 'Vicente Hizon Sr.',
            'Waan',
        ];

        $map = [];
        foreach ($near as $b) {
            $map[self::normalizeBarangay($b)] = 'near';
        }
        foreach ($mid as $b) {
            $map[self::normalizeBarangay($b)] = 'mid';
        }
        foreach ($far as $b) {
            $map[self::normalizeBarangay($b)] = 'far';
        }

        self::$zones = $map;
        return self::$zones;
    }
}
