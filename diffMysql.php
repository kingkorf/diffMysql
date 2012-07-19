<?php

$login1 = "";
$password1 = "";
$database1 = "";
$host1 = "";

$login2 = "";
$password2 = "";
$database2 = "";
$host2 = "";

/* @var $dbh PDO */
$dbh = NULL;

function &conect($dbname, $host, $user, $password) {

    $dbh = NULL;
    try {

        $dbh =& new PDO('mysql:dbname=' . $dbname . ';host=' . $host, $user, $password);
    } catch (PDOException $e) {

        $dbh = NULL;
        exit('Connection failed: ' . $e->getMessage());
    }
    return $dbh;
}

function getCampos($dbh, $tabela){
    
    $atributos = array();
    
    $query = $dbh->prepare("DESCRIBE ".$tabela);
    $query->execute();
    
    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $atributos[$row['Field']] = $row;
    }
    
    unset($query);
    
    return $atributos;
};

function getTabela($dbh) {

    $tabelas = array();
    
    $query = $dbh->prepare("SHOW TABLES");
    $query->execute();
    
    while( $row = $query->fetch(PDO::FETCH_NUM) ){
        $tabelas[$row[0]] = getCampos($dbh, $row[0]);
    }
    
    unset($query);
    
    return $tabelas;
}


$dbh =& conect($database2, $host2, $login2, $password2);
$online = getTabela($dbh);

$dbh = NULL;

$dbh =& conect($database1, $host1, $login1, $password1);
$local = getTabela($dbh);
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <title>Comparação DB</title>
    <style type="text/css">
    .normal,.n-exite,.modificada{background-color: gray;text-align: center;}
    .normal{ color: white; }
    .n-exite{color: red; }
    .modificada{color: blue;}
    </style>
</head>
<body>
<h2>Comparação DB</h2>
<table>
<?
foreach ($local as $key => $value) {
    if (array_key_exists($key, $online) == false) {
        
         echo "<tr class=\"n-exite\"><td>".$key."</td></tr>";
         

    }else if(count($value) > count($online[$key])){
        
        $fiels_on = $online[$key];
        
        echo "<tr class=\"modificada\"><td>".$key."</td><td> ALTER TABLE ".$key." \n\t\t";
        
        foreach ($value as $key => $value) {
            
            if(isset($fiels_on[$key]) === FALSE){
                echo "ADD ".$value['Field']." ".$value['Type']." ".($value['Null']=='YES'?'NULL':'NOT NULL').($value['Default']==NULL?" ".$value['Default']." ":" ").$value['Extra']; 
            }
        }
       
       echo "</td></tr>";              
    }else{
        
        echo "<tr class=\"normal\"><td>".$key."</td></tr>";
    }
}
?>
</table>
</body>
</html>
