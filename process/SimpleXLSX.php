<?php
/* SimpleXLSX v1.0 */

class SimpleXLSX {
    public static function parse($filename) {
        $xlsx = new self();
        return $xlsx->parseFile($filename) ? $xlsx : false;
    }

    private $sheets = array();

    public function sheets() {
        return $this->sheets;
    }

    public function rows($sheetIndex = 0) {
        return $this->sheets[$sheetIndex]['cells'] ?? [];
    }

    private function parseFile($filename) {
        if (!file_exists($filename)) return false;

        $zip = new ZipArchive();
        if ($zip->open($filename) === true) {

            // load shared strings
            $sharedStrings = array();
            if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
                $xml = simplexml_load_string($zip->getFromIndex($index));
                foreach ($xml->si as $item) {
                    $sharedStrings[] = (string) $item->t;
                }
            }

            // load sheets
            if (($index = $zip->locateName('xl/workbook.xml')) !== false) {
                $xml = simplexml_load_string($zip->getFromIndex($index));
                $sheetList = $xml->sheets->sheet;
            }

            foreach ($sheetList as $sheet) {
                $sheetName = (string)$sheet['name'];
                $sheetFile = 'xl/worksheets/' . $sheet['r:id'] . '.xml';

                foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname($filename), RecursiveDirectoryIterator::SKIP_DOTS)) as $file){
                    if (strpos($file, basename($sheetFile)) !== false){
                        $sheetPath = $file;
                        break;
                    }
                }

                $idx = $zip->locateName("xl/worksheets/sheet1.xml");
                if ($idx === false) continue;

                $xmlSheet = simplexml_load_string($zip->getFromIndex($idx));
                $rows = array();

                foreach ($xmlSheet->sheetData->row as $row) {
                    $cells = array();
                    foreach ($row->c as $c) {
                        $v = (string)$c->v;
                        if ($c['t'] == 's') {
                            $v = $sharedStrings[(int)$v] ?? '';
                        }
                        $cells[] = $v;
                    }
                    $rows[] = $cells;
                }

                $this->sheets[] = array(
                    'name' => $sheetName,
                    'cells' => $rows
                );
            }

            $zip->close();
            return true;
        }

        return false;
    }
}
?>
