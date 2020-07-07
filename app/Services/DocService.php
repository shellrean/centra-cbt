<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
class DocService
{
    private $filename;
    private $file;
    private $savepath = 'docimages';
    private $indexes = [ ];

	public function __construct($filePath) {
        $this->file = $filePath;
    }

    private function read_doc() {
        $fileHandle = fopen($this->filename, "r");
        $line = @fread($fileHandle, filesize($this->filename));   
        $lines = explode(chr(0x0D),$line);
        $outtext = "";
        foreach($lines as $thisline)
          {
            $pos = strpos($thisline, chr(0x00));
            if (($pos !== FALSE)||(strlen($thisline)==0))
              {
              } else {
                $outtext .= $thisline." ";
              }
          }
         $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
        return $outtext;
    }

    private function read_docx(){
        $striped_content = '';
        $content = '';

        $zip = zip_open($this->filename);

        if (!$zip || is_numeric($zip)) return false;

        while ($zip_entry = zip_read($zip)) {

            if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

            if (zip_entry_name($zip_entry) != "word/document.xml") continue;

            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        }// end while

        zip_close($zip);

        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $striped_content = strip_tags($content);

        return $striped_content;
    }

    public function convertToText() {
        if(isset($this->filename) && !file_exists($this->filename)) {
            return "File Not exists";
        }

        $fileArray = pathinfo($this->filename);
        $file_ext  = $fileArray['extension'];
        if($file_ext == "doc" || $file_ext == "docx")
        {
            if($file_ext == "doc") {
                return $this->read_doc();
            } else {
                return $this->read_docx();
            }
        } else {
            return "Invalid File Type";
        }
    }

    public function readZippedImages() {
        $zip = new \ZipArchive;
        
        $arra = array();

        if (true === $zip->open($this->filename)) {
            for ($i=0; $i<$zip->numFiles;$i++) {
                $zip_element = $zip->statIndex($i);
                if(preg_match("([^\s]+(\.(?i)(jpg|jpeg|png|gif|bmp))$)",$zip_element['name'])) {
                    // echo "<image src='display.php?filename=".$filename."&index=".$i."' /><hr />";
                    array_push($arra, $zip_element['name']);
                }
            }
        }

        return $arra;
    }

    function extractImages() {
        $ZipArchive = new \ZipArchive;
        if ( true === $ZipArchive->open( $this->file ) ) {
            for ( $i = 0; $i < $ZipArchive->numFiles; $i ++ ) {
                $zip_element = $ZipArchive->statIndex( $i );
                if ( preg_match( "([^\s]+(\.(?i)(jpg|jpeg|png|gif|bmp))$)", $zip_element['name'] ) ) {
                    $imagename                   = explode( '/', $zip_element['name'] );
                    $imagename                   = end( $imagename );
                    $this->indexes[ $imagename ] = $i;
                }
            }
        }
    }
    function saveAllImages() {
        if ( count( $this->indexes ) == 0 ) {
            echo 'No images found';
        }
        foreach ( $this->indexes as $key => $index ) {
            $zip = new \ZipArchive;
            if ( true === $zip->open( $this->file ) ) {
                // file_put_contents( dirname( __FILE__ ) . '/' . $this->savepath . '/' .    $key, $zip->getFromIndex( $index ) );
                Storage::disk('public')->put($this->savepath . '/' .    $key, $zip->getFromIndex( $index ) );
            }
            $zip->close();
        }
    }
    function displayImages() {
        $this->saveAllImages();
        if ( count( $this->indexes ) == 0 ) {
            return 'No images found';
        }
        $images = '';
        foreach ( $this->indexes as $key => $index ) {
            $path = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $this->savepath . '/' . $key;
            $images .= '<img src="' . $path . '" alt="' . $key . '"/> <br>';
        }
        echo $images;
     }
}