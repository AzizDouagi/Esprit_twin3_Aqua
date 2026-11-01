<?php

use Dompdf\Dompdf;
use Dompdf\Options;
use League\CommonMark\CommonMarkConverter;

require __DIR__.'/../vendor/autoload.php';
$root = realpath(__DIR__.'/..');
$input = $argv[1] ?? $root.'/docs/DEVOPS.md';
$output = $argv[2] ?? $root.'/docs/AquaSecure_DevOps.pdf';
if (! is_file($input)) {
    throw new RuntimeException('Document source introuvable.');
}
$converter = new CommonMarkConverter(['html_input' => 'strip', 'allow_unsafe_links' => false]);
$html = (string) $converter->convert(file_get_contents($input));
$html = preg_replace_callback('/src="(diagrams\/[^"<>]+\.png)"/', function (array $match) use ($root): string {
    $path = $root.'/docs/'.$match[1];
    if (! is_file($path)) {
        throw new RuntimeException('Diagramme manquant : lancer scripts/render-diagrams.sh');
    }

    return 'src="'.htmlspecialchars($path, ENT_QUOTES).'"';
}, $html);
$options = new Options;
$options->set('isRemoteEnabled', false);
$options->set('chroot', [$root.'/docs']);
$options->set('defaultFont', 'DejaVu Sans');
$pdf = new Dompdf($options);
$pdf->setPaper('A4');
$pdf->loadHtml('<!doctype html><html lang="fr"><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#0f172a;line-height:1.4}h1{font-size:22px;color:#075985}h2{font-size:15px;color:#075985;border-bottom:1px solid #cbd5e1;page-break-after:avoid}h3{font-size:12px;page-break-after:avoid}img{max-width:100%;max-height:500px}pre{background:#f1f5f9;padding:8px;white-space:pre-wrap;font-size:8px}code{font-size:8px}table{width:100%;border-collapse:collapse}td,th{border:1px solid #cbd5e1;padding:4px;text-align:left}a{color:#075985}tr{page-break-inside:avoid}</style></head><body>'.$html.'</body></html>');
$pdf->render();
if (file_put_contents($output, $pdf->output()) === false) {
    throw new RuntimeException('Écriture PDF impossible.');
}
echo basename($output).' : '.$pdf->getCanvas()->get_page_count()." page(s)\n";
