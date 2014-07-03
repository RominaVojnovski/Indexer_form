<?PHP
header("content-type:application/json");
//globals
$lastarray=array();
$countd=0;
$delimiters="\"\|\$\*\#\^%1234567890\0\[\]\{\}<>@_=+&\;\//-?:.,!\~\`\'\(\) ";

//FUNCTIONS
//superexplode method
function superexplode($str, $sep){
            $i=0;
            $arr[$i++]=strtok($str, $sep);
            while(($token=strtok($sep))!== FALSE){
                $arr[$i++]=$token;
            }
            return $arr;
}

//check extension 
function filename_extension($filename) {
    $pos = strrpos($filename, '.');
    if($pos===false) {
        return false;
    } else {
        return substr($filename, $pos+1);
    }
}

function traverse_dir($mydir){
    
    global $lastarray;
    global $delimiters;
    global $countd;
    
    chdir($mydir);
    
    $godir=getcwd();
    
    if($d = opendir($godir)){
        
        //echo "BEGINNING OF READING A DIRECTORY: ".$godir."\n";
        
        while(($f = readdir($d))!==false){
            
            
            if(((strcmp($f,".."))!= 0) && ((strcmp($f,"."))!= 0)){
                if(!is_dir($f)){ 
                    
                    if((filename_extension($f) == "html")||(filename_extension($f) == "htm")){ 
                        $tags=get_meta_tags($f);
                        $larray=array('FILE NAME' => (strtoupper($f)),'META' => 'TAGS');
                        
                        if(($tags)){
                            
                            while (list($key, $val) = each($tags)) {
                                $larray+=array(utf8_encode($key) => strval($val));        
                            }  
                        }
                        else{
                            $larray+=array('NONE' => 'NONE');
                            
                        }
                        $larray+=array('*********************' => '*********************','WORD' => 'COUNT');
                        
                        while (list($key, $val) = each($larray)) {
                            $lastarray[]=array(utf8_encode($key) => strval($val));        

                        }
                       
                        $godir2=getcwd();
                        $h=fopen($f,"r");
                        if($h){
                            $y=0;
    
                            while (($buf = fgets($h)) !== false) {
                                $buf2=trim((strtolower(strip_tags($buf ))));
                                
                                if($buf2 !==""){                
                                    $myarray[$y]=superexplode($buf2,$delimiters);
                                    $y++;
                                }
                            }
                            foreach($myarray as $val){
                                foreach($val as $s){              
                                    if(isset($newarray[$s])){
                                        $newarray[$s]++;
                                    }
                                    else{
                                        $newarray[$s]=1;
                                    }
                                }
                            }
                            ksort($newarray);
                            $newarray+=array('*********************' => '*********************','******************' => '******************','*****EOF*****' => '*****EOF*****','************' => '************','*********' => '*********'); 
                        }//end if $handle true
                        else{
                            
                        }
                        
                        while (list($key, $val) = each($newarray)) {
                            $lastarray[]=array(utf8_encode($key) => strval($val));        

                        }
                        
                        
                    }// END IF FILE IS AN HTM OR HTML
                   
                }//END IF FILE IS NOT DIRECTORY
                else{
                    $countd++;
                    
                    $temp=getcwd();
                    $temp=$temp."\\".$f;
                    //echo "going one level deeper to: ".$temp."\n";
                    traverse_dir($temp);
                }
            }
            
        }//while
       
        if($countd>0){
            $current=getcwd();
            $pos= strrpos($current,"\\");
            $dummy=substr($current,0,$pos);
            chdir($dummy);
            $countd--;
            
            
            
            /*while($countd>0){
                $current=getcwd();
                $pos= strrpos($current,"\\");
                $dummy=substr($current,0,$pos);
                chdir($dummy);
                $countd--;
            }
            */
        }
        
    }//if able to open for reading
    else{
        echo "not able to open for reading \n";
    }


}//end function traverse_dir


//****END FUNCTIONS BLOCK****

$pageval=$_POST['page'];

$kind= $_POST['kind'];


if($kind == "pinfo"){
  
    if(is_dir($pageval)){
        //echo "calling traverse dir method on directory... \n";
        traverse_dir($pageval);
        //echo "just came out of traverse dir method... \n";
        //print_r($lastarray);
        echo json_encode($lastarray,JSON_FORCE_OBJECT);
        exit();
    }
    
    //gets all the meta tags from page
    if((filename_extension($pageval) == "html")||(filename_extension($pageval) == "htm")){
    
        $tags=get_meta_tags($pageval);
        $pg = array();
        $pg=array('FILE NAME' => $pageval,'META' => 'TAGS');
    
            if($tags){
                while (list($key, $val) = each($tags)) {
                    $pg+=array(utf8_encode($key) => strval($val));        
                }
            }
            else{
                $pg+=array('NA' => 'NA');
            }
    
        $pg+=array('*********************' => '*********************','****************' => '****************','*************' => '*************','WORD' => 'COUNT');
    
        //reads file and checks word count line by line
        $handle=fopen($pageval,"r");
        
        if($handle){
            $y=0;
            while (($buffer = fgets($handle)) !== false) {
                $buffer2=trim((strtolower(strip_tags($buffer))));
                if($buffer2 !==""){                
                    $myarray[$y]=superexplode($buffer2,$delimiters);
                    //insert each new word into array and add count to its second parameter 2 d array
                    $y++;
                }
            }
        
            //loop thru myarray for each value in $myarray[i] if it exists in newarray[] then add to counter else push
        
            foreach($myarray as $val){
                //inner loop for line
                foreach($val as $s){              
                    //if word exists in newarray add to the counter
                    if(isset($newarray[$s])){
                        $newarray[$s]++;
                    }
                    else{
                        $newarray[$s]=1;
                    }
                }
            }
        
            ksort($newarray);
            $newarray+=array('*********************' => '*********************','******************' => '******************','*****EOF*****' => '*****EOF*****','************' => '************','*********' => '*********'); 
        
        
        }//end if $handle true
        else{
            $pg1=array(
            array
            (
                'NA' => 'NA'
            )
            );
            $final=$pg+$pg1;
            echo json_encode($pg1,JSON_FORCE_OBJECT);
            exit();
        }
        $pg1 = array();

        while (list($key, $val) = each($pg)) {
            //echo "inside while...\n";
            //echo "key is ".$key." and val is ".$val."\n";
            $pg1[]=array(utf8_encode($key) => strval($val));        
        }
 
        while (list($key, $val) = each($newarray)) { 
            $pg1[]=array(utf8_encode($key) => strval($val));        
        }
  
        if(count($pg1) > 0){
            $final = $pg1;
            echo json_encode($final,JSON_FORCE_OBJECT); 
        }
    
    }//end if file is .html or .htm
    else{
        $final=array(
            array
            (
                'NA' => 'NA'
            )
            );
        echo json_encode($final,JSON_FORCE_OBJECT);
    }
   
}//en if kind is p info

exit();

?>
