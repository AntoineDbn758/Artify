<?php

/**
 * Wrapper minimal autour de l'API Stripe. Volontairement sans SDK officiel
 * pour ne pas ajouter de dependance composer : on parle directement a
 * https://api.stripe.com via curl en envoyant des donnees form-urlencoded.
 * Fonctions principales : stripe_configured() pour savoir si les cles d'env
 * sont presentes, stripe_create_checkout() pour ouvrir une session Stripe
 * Checkout. Les cles sont lues depuis STRIPE_PUBLISHABLE_KEY et
 * STRIPE_SECRET_KEY.
 */

/**
 * Wrapper minimal Stripe API (sans SDK).
 * Lit STRIPE_SECRET_KEY et STRIPE_PUBLISHABLE_KEY depuis l'environnement.
 * Utilise uniquement curl, pas de dépendance composer.
 */

function stripe_keys(): array {
    return [
        'pk' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
        'sk' => getenv('STRIPE_SECRET_KEY')      ?: '',
    ];
}

// On verifie le prefixe pour eviter d'envoyer des appels avec
// des cles manifestement invalides (typo ou variable vide).
function stripe_configured(): bool {
    $k = stripe_keys();
    return $k['sk'] !== '' && $k['pk'] !== ''
        && str_starts_with($k['sk'], 'sk_')
        && str_starts_with($k['pk'], 'pk_');
}

function stripe_is_test_mode(): bool {
    $k = stripe_keys();
    return str_starts_with($k['sk'], 'sk_test_');
}

/**
 * POST application/x-www-form-urlencoded à l'API Stripe.
 * Stripe attend les arrays en notation crochets : line_items[0][price_data][...]
 */
function stripe_post(string $endpoint, array $data): array {
    $k = stripe_keys();
    // Auth Stripe = Basic avec la cle secrete en username,
    // mot de passe vide (convention de l'API).
    $ch = curl_init("https://api.stripe.com/v1/$endpoint");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => stripe_build_query($data),
        CURLOPT_USERPWD        => $k['sk'] . ':',
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 20,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($body === false) {
        return ['_error' => "curl: $err", '_http_code' => 0];
    }
    $json = json_decode($body, true) ?: [];
    $json['_http_code'] = $code;
    return $json;
}

/**
 * GET application/x-www-form-urlencoded à l'API Stripe.
 */
function stripe_get(string $endpoint): array {
    $k = stripe_keys();
    $ch = curl_init("https://api.stripe.com/v1/$endpoint");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => $k['sk'] . ':',
        CURLOPT_TIMEOUT        => 20,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false) return ['_http_code' => 0];
    $json = json_decode($body, true) ?: [];
    $json['_http_code'] = $code;
    return $json;
}

/**
 * Sérialise un array PHP en notation Stripe (clé[sous-clé]=valeur).
 * Stripe n'accepte pas le JSON mais x-www-form-urlencoded avec
 * cette convention d'imbrication.
 */
function stripe_build_query(array $data, string $prefix = ''): string {
    $parts = [];
    foreach ($data as $k => $v) {
        $key = $prefix === '' ? $k : "{$prefix}[{$k}]";
        // Recursion pour aplatir les sous tableaux en cle[sous-cle].
        if (is_array($v)) {
            $parts[] = stripe_build_query($v, $key);
        } else {
            $parts[] = rawurlencode($key) . '=' . rawurlencode((string)$v);
        }
    }
    return implode('&', array_filter($parts, fn($p) => $p !== ''));
}

/**
 * Crée une session Stripe Checkout pour un ensemble de produits.
 * $items : [['nom'=>..., 'prix'=>123.45, 'quantite'=>1, 'image_url'=>...], ...]
 * Renvoie l'URL de redirection vers la page Stripe.
 */
function stripe_create_checkout(array $items, string $success_url, string $cancel_url, ?string $customer_email = null): array {
    // Stripe attend les montants en centimes et le nom du produit
    // limite a 100 caracteres, sinon l'API renvoie une erreur 400.
    $line_items = [];
    foreach ($items as $i => $it) {
        $line_items[$i] = [
            'price_data' => [
                'currency'     => 'eur',
                'unit_amount'  => (int)round(((float)$it['prix']) * 100), // centimes
                'product_data' => [
                    'name' => substr($it['nom'], 0, 100),
                ],
            ],
            'quantity' => max(1, (int)($it['quantite'] ?? 1)),
        ];
        // Image transmise seulement si l'URL est valide, sinon Stripe rejette.
        if (!empty($it['image_url']) && filter_var($it['image_url'], FILTER_VALIDATE_URL)) {
            $line_items[$i]['price_data']['product_data']['images'] = [$it['image_url']];
        }
    }
    $data = [
        'mode'        => 'payment',
        'line_items'  => $line_items,
        'success_url' => $success_url,
        'cancel_url'  => $cancel_url,
        'locale'      => 'fr',
    ];
    if ($customer_email) $data['customer_email'] = $customer_email;
    return stripe_post('checkout/sessions', $data);
}
