<?php
/*
 * Gestionnaire XLSX natif pour TrackObjet.
 * Extensions requises : ZipArchive et SimpleXML.
 * Les lignes sont des tableaux associatifs avec les colonnes A, B, C...
 */

const TRACK_XLSX_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
const TRACK_XLSX_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
const TRACK_XLSX_PACKAGE_REL = 'http://schemas.openxmlformats.org/package/2006/relationships';

function xlsxColonneLettre(int $index): string
{
    $lettre = '';
    $index++;

    while ($index > 0) {
        $reste = ($index - 1) % 26;
        $lettre = chr(65 + $reste) . $lettre;
        $index = intdiv($index - 1, 26);
    }

    return $lettre;
}

function xlsxReferenceVersColIndex(string $reference): int
{
    if (!preg_match('/^([A-Z]+)/i', $reference, $matches)) {
        return 0;
    }

    $index = 0;
    foreach (str_split(strtoupper($matches[1])) as $lettre) {
        $index = $index * 26 + ord($lettre) - 64;
    }

    return $index - 1;
}

function xlsxXml(string $contenu, string $nom): SimpleXMLElement
{
    $xml = simplexml_load_string($contenu, SimpleXMLElement::class, LIBXML_NONET);
    if ($xml === false) {
        throw new RuntimeException("XML invalide : " . $nom);
    }
    return $xml;
}

function xlsxTexte(SimpleXMLElement $element): string
{
    $ns = $element->children(TRACK_XLSX_MAIN);
    if (isset($ns->t)) {
        return (string) $ns->t;
    }

    $texte = '';
    foreach ($ns->r as $run) {
        $texte .= (string) ($run->children(TRACK_XLSX_MAIN)->t ?? '');
    }
    return $texte;
}

function xlsxCheminRelation(string $cible): string
{
    $cible = str_replace('\\', '/', $cible);
    $chemin = str_starts_with($cible, '/') ? ltrim($cible, '/') : 'xl/' . $cible;
    $parties = [];

    foreach (explode('/', $chemin) as $partie) {
        if ($partie === '' || $partie === '.') continue;
        if ($partie === '..') {
            array_pop($parties);
        } else {
            $parties[] = $partie;
        }
    }

    return implode('/', $parties);
}

/*
 * Retourne les deux feuilles, en-têtes compris.
 * Si le fichier n'existe pas encore, retourne deux feuilles vides.
 */
function xlsxLireClasseurComplet(string $fichier): array
{
    $noms = ['Objets perdus', 'Objets retrouvés'];
    $resultat = ['Objets perdus' => [], 'Objets retrouvés' => []];

    if (!file_exists($fichier)) return $resultat;
    if (!class_exists(ZipArchive::class)) {
        throw new RuntimeException("ZipArchive n'est pas activé.");
    }

    $zip = new ZipArchive();
    if ($zip->open($fichier) !== true) {
        throw new RuntimeException("Impossible d'ouvrir declarations.xlsx.");
    }

    try {
        $wbData = $zip->getFromName('xl/workbook.xml');
        $relData = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($wbData === false || $relData === false) {
            throw new RuntimeException("Le fichier n'est pas un classeur XLSX valide.");
        }

        $wb = xlsxXml($wbData, 'workbook.xml');
        $rels = xlsxXml($relData, 'workbook.xml.rels');

        $cibles = [];
        foreach ($rels->children(TRACK_XLSX_PACKAGE_REL)->Relationship as $rel) {
            $a = $rel->attributes();
            $cibles[(string) $a['Id']] = xlsxCheminRelation((string) $a['Target']);
        }

        $fichiersFeuilles = [];
        foreach ($wb->children(TRACK_XLSX_MAIN)->sheets->children(TRACK_XLSX_MAIN)->sheet as $sheet) {
            $a = $sheet->attributes();
            $r = $sheet->attributes(TRACK_XLSX_REL);
            $nom = (string) $a['name'];
            $id = (string) $r['id'];

            if (in_array($nom, $noms, true) && isset($cibles[$id])) {
                $fichiersFeuilles[$nom] = $cibles[$id];
            }
        }

        $partages = [];
        $sharedData = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedData !== false) {
            $shared = xlsxXml($sharedData, 'sharedStrings.xml');
            foreach ($shared->children(TRACK_XLSX_MAIN)->si as $si) {
                $partages[] = xlsxTexte($si);
            }
        }

        foreach ($noms as $nom) {
            if (!isset($fichiersFeuilles[$nom])) continue;

            $data = $zip->getFromName($fichiersFeuilles[$nom]);
            if ($data === false) {
                throw new RuntimeException("Impossible de lire la feuille " . $nom);
            }

            $sheetXml = xlsxXml($data, $nom);
            $sheetData = $sheetXml->children(TRACK_XLSX_MAIN)->sheetData;

            foreach ($sheetData->children(TRACK_XLSX_MAIN)->row as $rowXml) {
                $ligne = [];

                foreach ($rowXml->children(TRACK_XLSX_MAIN)->c as $cell) {
                    $a = $cell->attributes();
                    $ref = (string) ($a['r'] ?? '');
                    if ($ref === '') continue;

                    $col = xlsxColonneLettre(xlsxReferenceVersColIndex($ref));
                    $type = (string) ($a['t'] ?? '');
                    $valeurs = $cell->children(TRACK_XLSX_MAIN);

                    if ($type === 's') {
                        $i = (int) ($valeurs->v ?? -1);
                        $valeur = $partages[$i] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $valeur = isset($valeurs->is) ? xlsxTexte($valeurs->is) : '';
                    } else {
                        $valeur = (string) ($valeurs->v ?? '');
                    }

                    $ligne[$col] = $valeur;
                }

                if ($ligne) $resultat[$nom][] = $ligne;
            }
        }

        return $resultat;
    } finally {
        $zip->close();
    }
}

/* Lit une feuille sans sa ligne d'en-têtes. */
function lireFeuilleXlsx(string $fichier, string $nomFeuille): array
{
    $classeur = xlsxLireClasseurComplet($fichier);
    if (!isset($classeur[$nomFeuille])) {
        throw new InvalidArgumentException("Feuille inconnue : " . $nomFeuille);
    }

    $lignes = $classeur[$nomFeuille];
    if ($lignes) array_shift($lignes);
    return $lignes;
}

function xlsxEchapper($valeur): string
{
    $texte = (string) ($valeur ?? '');
    $texte = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $texte) ?? '';
    return htmlspecialchars($texte, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function xlsxCreerXmlFeuille(array $lignes): string
{
    $xmlLignes = '';
    $numero = 0;

    foreach ($lignes as $ligne) {
        $numero++;
        $cellules = [];
        $triees = [];

        foreach ($ligne as $colonne => $valeur) {
            if (is_int($colonne) || ctype_digit((string) $colonne)) {
                $index = (int) $colonne;
                $lettre = xlsxColonneLettre($index);
            } else {
                $lettre = strtoupper((string) $colonne);
                $index = xlsxReferenceVersColIndex($lettre . '1');
            }
            $triees[$index] = [$lettre, $valeur];
        }

        ksort($triees);
        foreach ($triees as [$lettre, $valeur]) {
            $ref = $lettre . $numero;
            $cellules[] = '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">' .
                xlsxEchapper($valeur) . '</t></is></c>';
        }

        $xmlLignes .= '<row r="' . $numero . '">' . implode('', $cellules) . '</row>';
    }

    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<worksheet xmlns="' . TRACK_XLSX_MAIN . '"><sheetData>' .
        $xmlLignes . '</sheetData></worksheet>';
}

/*
 * Écrit/reconstruit le classeur complet.
 * IMPORTANT : transmettre les deux feuilles, en-têtes compris.
 */
function ecrireClasseurXlsx(string $fichier, array $feuilles): bool
{
    $noms = ['Objets perdus', 'Objets retrouvés'];
    foreach ($noms as $nom) {
        if (!isset($feuilles[$nom]) || !is_array($feuilles[$nom])) {
            throw new InvalidArgumentException("Feuille manquante : " . $nom);
        }
    }

    $dossier = dirname($fichier);
    if (!is_dir($dossier) || !is_writable($dossier)) {
        throw new RuntimeException("Le dossier du classeur n'est pas accessible en écriture.");
    }

    $temp = $fichier . '.tmp_' . bin2hex(random_bytes(5));
    $zip = new ZipArchive();

    if ($zip->open($temp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException("Impossible de créer le classeur temporaire.");
    }

    $contenus = [
        '[Content_Types].xml' =>
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '</Types>',

        '_rels/.rels' =>
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="' . TRACK_XLSX_PACKAGE_REL . '">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>',

        'xl/workbook.xml' =>
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="' . TRACK_XLSX_MAIN . '" xmlns:r="' . TRACK_XLSX_REL . '">' .
            '<sheets><sheet name="Objets perdus" sheetId="1" r:id="rId1"/>' .
            '<sheet name="Objets retrouvés" sheetId="2" r:id="rId2"/></sheets></workbook>',

        'xl/_rels/workbook.xml.rels' =>
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="' . TRACK_XLSX_PACKAGE_REL . '">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>' .
            '</Relationships>',

        'xl/worksheets/sheet1.xml' => xlsxCreerXmlFeuille($feuilles['Objets perdus']),
        'xl/worksheets/sheet2.xml' => xlsxCreerXmlFeuille($feuilles['Objets retrouvés'])
    ];

    try {
        foreach ($contenus as $nom => $contenu) {
            if (!$zip->addFromString($nom, $contenu)) {
                throw new RuntimeException("Erreur d'écriture dans le classeur : " . $nom);
            }
        }

        if (!$zip->close()) {
            throw new RuntimeException("Impossible de finaliser le classeur XLSX.");
        }

        $ancien = null;
        if (file_exists($fichier)) {
            $ancien = $fichier . '.ancien_' . bin2hex(random_bytes(4));
            if (!rename($fichier, $ancien)) {
                throw new RuntimeException("Impossible de mettre l'ancien classeur de côté.");
            }
        }

        if (!rename($temp, $fichier)) {
            if ($ancien !== null && file_exists($ancien)) rename($ancien, $fichier);
            throw new RuntimeException("Impossible de remplacer le classeur.");
        }

        if ($ancien !== null && file_exists($ancien)) unlink($ancien);
        return true;

    } catch (Throwable $erreur) {
        if (file_exists($temp)) unlink($temp);
        throw $erreur;
    }
}

function enregistrerDeclaration(array $donnees)
{
    $fichier = __DIR__ . '/declarations.xlsx';

    try {
        // Lire les deux feuilles sans perdre les anciennes données.
        $feuilles = xlsxLireClasseurComplet($fichier);

        // Définir les en-têtes exacts des deux feuilles.
        $entetesPerdus = [
            'A' => 'N°',
            'B' => 'Date',
            'C' => 'Formulaire',
            'D' => 'Nom',
            'E' => 'Type objet',
            'F' => 'Description',
            'G' => 'WhatsApp',
            'H' => 'Telephone'
        ];

        $entetesRetrouves = [
            'A' => 'N°',
            'B' => 'Date',
            'C' => 'Formulaire',
            'D' => 'Nom',
            'E' => 'Type objet',
            'F' => 'WhatsApp',
            'G' => 'Telephone',
            'H' => 'Adresse',
            'I' => 'Photos'
        ];

        // Initialiser les en-têtes si les feuilles sont vides.
        if (empty($feuilles['Objets perdus'])) {
            $feuilles['Objets perdus'][] = $entetesPerdus;
        }

        if (empty($feuilles['Objets retrouvés'])) {
            $feuilles['Objets retrouvés'][] = $entetesRetrouves;
        }

        // Choisir la feuille et préparer la déclaration.
        if (($donnees['formulaire'] ?? '') === 'PERDU') {

            $nomFeuille = 'Objets perdus';

            $ligne = [
                'A' => count($feuilles[$nomFeuille]),
                'B' => date('d/m/Y H:i:s'),
                'C' => $donnees['formulaire'],
                'D' => $donnees['nom'],
                'E' => $donnees['type'],
                'F' => $donnees['description'],
                'G' => $donnees['whatsapp'],
                'H' => $donnees['telephone']
            ];

        } elseif (($donnees['formulaire'] ?? '') === 'RETROUVE') {

            $nomFeuille = 'Objets retrouvés';

            $ligne = [
                'A' => count($feuilles[$nomFeuille]),
                'B' => date('d/m/Y H:i:s'),
                'C' => $donnees['formulaire'],
                'D' => $donnees['nom'],
                'E' => $donnees['type'],
                'F' => $donnees['whatsapp'],
                'G' => $donnees['telephone'],
                'H' => $donnees['adresse'],
                'I' => $donnees['photos']
            ];

        } else {
            throw new InvalidArgumentException('Type de déclaration inconnu.');
        }

        // Ajouter la nouvelle déclaration à la bonne feuille.
        $feuilles[$nomFeuille][] = $ligne;

        // Réécrire les deux feuilles dans le même classeur.
        ecrireClasseurXlsx($fichier, $feuilles);

        return $ligne['A'];

    } catch (Throwable $erreur) {
        error_log('Erreur Excel TrackObjet : ' . $erreur->getMessage());
        return false;
    }
}

?>
