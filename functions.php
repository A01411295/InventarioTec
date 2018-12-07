<?php
function getUser($mail, $password){
    $pdo = $GLOBALS["pdo"];
    $stmt = $pdo->prepare('SELECT id, nombre, mail, rol from users where mail = :mail and password = :password');
    $stmt->execute(['mail' => $mail, 'password'=>$password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user;
}
function buildCardContainer(){
    $pdo = $GLOBALS["pdo"];
    $stmt = $pdo->prepare('SHOW tables');
    $stmt->execute();
    $tables = array();
    while($row = $stmt->fetch(PDO::FETCH_NUM)){
        array_push($tables, $row[0]);
    }
    $tablesAndColumns = array();
    foreach ($tables as $faKey => $table) {
        if($_SESSION["user"]["rol"] == 0 && $table == "users") continue;
        array_push($tablesAndColumns, array("title" => ucfirst($table), "name" => $table, "columns" => getTableCols(getColumnNames($table))));
    }
    return json_encode($tablesAndColumns);

}
function getColumnNames($table){
    $pdo = $GLOBALS["pdo"];
    $stmt = $pdo->prepare('DESCRIBE '.$table);
    $stmt->execute();
    $columns = array();
    while($row = $stmt->fetch(PDO::FETCH_NUM)){
 
        array_push($columns, $row[0]);
    }
    return $columns;
}
function getTableCols($columnNames){
    $columnTitles = array();
    foreach ($columnNames as $faKey => $columnName) {
        switch ($columnName) {
            case 'articulo_id':
                $columnName="Artículo";
                break;
            case 'users_id':
                $columnName="Responsable";
                break;
            default:
                $columnName = ucfirst($columnName);
                break;
        }
        if(!($_SESSION["user"]["rol"]==0 && $columnName =="Id")){
            array_push($columnTitles, $columnName);
        }
    }
    return $columnTitles;
}
function getColumnSpecs($table){
    $pdo = $GLOBALS["pdo"];
    $stmt = $pdo->prepare('DESCRIBE '.$table);
    $stmt->execute();
    $columns = array();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $columns[] = $row;
    }
    return $columns;
}
function getTableData($table){
    $pdo = $GLOBALS["pdo"];
    if($_SESSION["user"]["rol"]==0 && $table=="historial"){
        $stmt = $pdo->prepare('SELECT * FROM '.$table.' where users_id = '.$_SESSION["user"]["id"]);
    }else if($_SESSION["user"]["rol"] == 0 && $table == "articulo"){
        $stmt = $pdo->prepare('SELECT * FROM '.$table.' where estado = 0');
    }else{
        $stmt = $pdo->prepare('SELECT * FROM '.$table);
    }
    $stmt->execute();
    $data = array();
    if($table == "historial"){
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $row["articulo_id"] = getNameFromId("articulo", $row["articulo_id"]);
            
            if($row["accion"]==0){
                $row["accion"]="devolución";
            }else{
                $row["accion"]="arrendamiento";
            }
            $row["imagen"] = '<img class="img-fluid" src="images/'.$row["imagen"].'" alt="imagen no disp">';
            $edit = '<div><a class="edit btn btn-block btn-outline-success" id="'.$table.'-'.$row["id"].'-edit"> <i class="fa fa-edit"></i> </a></div>';
            if($_SESSION["user"]["rol"]==0){
                unset($row["users_id"]);
                unset($row["id"]);
            }else{

                $row["users_id"] = getNameFromId("users", $row["users_id"]);
            }
            $row = array_values($row);
            if($_SESSION["user"]["rol"]==1){
                array_push($row, $edit);
            }
            $data[] = $row;
        }

    }else{
        while($row = $stmt->fetch(PDO::FETCH_NUM)){
            if($table == "users"){
                if($row[4]==0){
                    $row[4]="estudiante";
                }else{
                    $row[4]="administrativo";
                }
                array_splice($row,3,1);
            }
            if($table == "articulo"){
                if($row[2]==0){
                    $row[2]="disponible";
                }else{
                    $row[2]="no disponible";
                }
            }
            if($_SESSION["user"]["rol"]==0 && $table=="articulo"){
                $edit = '<div><a class="arrendar btn btn-block btn-outline-info" id="historial-'.$row[0].'-arrendar"> <i class="far fa-hand-paper"></i> </a></div>';
            }else{
                $edit = '<div><a class="edit btn btn-block btn-outline-success" id="'.$table.'-'.$row[0].'-edit"> <i class="fa fa-edit"></i> </a></div>';
            }
            if($_SESSION["user"]["rol"]==0){array_splice($row,0,1);}
            array_push($row, $edit);
            $data[] = $row;
        }
    }
    return json_encode(array("data" => $data));
}
function getNameFromId($table, $id){
    $pdo = $GLOBALS["pdo"];
    $stmt = $pdo->prepare('SELECT nombre FROM '.$table.' where id = '.$id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)["nombre"];
}
function createForm($model){
    $namesAndTypes = getColumnSpecs($model);
    $form= '<form id="modalForm" action="routes.php" enctype="multipart/form-data" method="POST" role="form">
    <input type="hidden" name="formType" value="insert">
    <input type = "hidden" name="table_model" value="'.$model.'">
                <div class="modal-body">
                <div class="row">';
    foreach ($namesAndTypes as $faKey => $field) {
        if($field["Field"] == "id" || $field["Field"] == "fecha") continue;
        if($field["Null"] == "NO"){
            $required = "required";
        }else{
            $required ="";
        }
        if($field["Field"]== "imagen"){
            $form .= '<div class="form-group col-12">';
        }else{
            $form .= '<div class="form-group col-6">';
        }
        
        if($field["Key"] ==="MUL" ){
            $form.='<label>'.explode("_", $field["Field"])[0].'</label>
            <select '.$required.' name="'.htmlspecialchars($field["Field"]).'" class="form-control">';
            $selectOptions = getOptions($field["Field"]);
            foreach ($selectOptions as $faKey2 => $option) {
                $form.='<option value="'.htmlspecialchars($option[0]).'">'.htmlspecialchars($option[1]).'</option>';
            }
            $form.="</select>";
        }
        else{
            if(strpos($field["Type"], 'varchar') !== false){
                $type="text";
                if($field["Field"]=="email" || $field["Field"]=="password"){
                    $type=$field["Field"];
                }
            }else{
                $type ="number";
            }
            
            switch ($field["Field"]) {
                case 'imagen':
                $form.='<label for="field-'.htmlspecialchars($field["Field"]).'">'.htmlspecialchars($field["Field"]).'</label>';
                $form.='<div class="input-group">
                <div class="custom-file">
                <input '.htmlspecialchars($required).' type="file" name="'.htmlspecialchars($field["Field"]).'" class="custom-file-input" id="'.$field["Field"].'">
                <label class="custom-file-label" for="'.$field["Field"].'">Foto</label>
                </div>
                </div>';
                    break;
                case 'rol':
                $form.='<label>'.htmlspecialchars($field["Field"]).'</label>
                <select '.$required.' name="'.htmlspecialchars($field["Field"]).'" class="form-control">';
                    $form.='<option value="0">estudiante</option>';
                    $form.='<option value="1">administrativo</option>';
                $form.="</select>";
                    break;
                case 'estado':
                    $form.='<label>'.htmlspecialchars($field["Field"]).'</label>
                    <select '.$required.' name="'.htmlspecialchars($field["Field"]).'" class="form-control">';
                    $form.='<option value="0">disponible</option>';
                    $form.='<option value="1">no disponible</option>';
                    $form.="</select>";
                    break;
                case 'accion':
                    $form.='<label>'.htmlspecialchars($field["Field"]).'</label>
                    <select '.$required.' name="'.htmlspecialchars($field["Field"]).'" class="form-control">';
                    $form.='<option value="0">devolución</option>';
                    $form.='<option value="1">arrendamiento</option>';
                    $form.="</select>";
                    break;
                default:
                $form.='<label for="field-'.htmlspecialchars($field["Field"]).'">'.htmlspecialchars($field["Field"]).'</label>';
                $form.='<input '.htmlspecialchars($required).' type="'.htmlspecialchars($type).'" name="'.htmlspecialchars($field["Field"]).'" class="form-control" placeholder="Enter '.htmlspecialchars($field["Field"]).'">';
                    break;
            }
            

            if($field["Field"]=="imagen"){
                
            }else{
                
            }
       }
       $form.="</div>";
    }
    $form.='</div>
        <div class="modal-footer">
        <button type="submit" id="submitButton" class="btn btn-primary">Submit</button>
    </div>
    </form>';

    return $form;
    
}
function getOptions($field){
    $table = explode("_", $field)[0];
    $pdo = $GLOBALS["pdo"];
    $stmt = $pdo->prepare('SELECT * FROM '.$table);
    $stmt->execute();
    $options = array();
    while($row = $stmt->fetch(PDO::FETCH_NUM)){
        array_push($options, $row);
    }
    return $options;
}
function insertRegister(){
    if(isset($_FILES['imagen'])){
        $info = pathinfo($_FILES['imagen']['name']);
        $ext = $info['extension']; // get the extension of the file
        $newname = "user".$_POST["users_id"]."_articulo".$_POST["articulo_id"]."_".time().".".$ext; 
        $_POST["imagen"]=$newname;
        $target = 'images/'.$newname;
        move_uploaded_file( $_FILES['imagen']['tmp_name'], $target);
    }
    $table = $_POST["table_model"];
    $cols = getColumnNames($table);
    $colsAndValues = array();
    foreach ($cols as $faKey => $colName) {
        if(isset($_POST[$colName])){
            array_push($colsAndValues, array("col" => $colName, "val" => $_POST[$colName]));
        }
    }

    $queryCols ="(";
    $queryVals ="(";
    foreach ($colsAndValues as $faKey2 => $combo) {
        $queryCols.=$combo["col"].", ";
        $queryVals.=":".$combo["col"].", ";
    }
    $queryCols = substr($queryCols, 0, -2);
    $queryCols.=")";
    $queryVals=substr($queryVals, 0, -2);
    $queryVals.=")";
    $pdo = $GLOBALS["pdo"];
    $stmt = $pdo->prepare('Insert into '.$table.' '.$queryCols.' Values '.$queryVals);
    foreach ($colsAndValues as $faKey3 => $combo) {
        $stmt->bindValue(":".$combo["col"], $combo["val"]);
    }
    try{
        $stmt->execute();
        if ($table == "historial"){
            return changeEstadoArticulo($_POST["articulo_id"], $_POST["accion"]);
        }else{
            return "Registro insertado correctamente";
        }
    } catch (PDOException $e) {
        return "Error insertando registro";
    }
}
function changeEstadoArticulo($articuloId, $estado){
    $pdo = $GLOBALS["pdo"];
    $stmt = $pdo->prepare("UPDATE articulo SET estado = ".$estado." where id = ".$articuloId);
    try{
        $stmt->execute();
        return "Registro insertado correctamente";
    }catch (PDOException $e) {
        return "Error insertando registro";
    }
}
function editForm($table, $id){
    $register = getRegister($table, $id);
    $namesAndTypes = getColumnSpecs($table);
    $form= '<form id="modalForm" action="routes.php" enctype="multipart/form-data" method="POST" role="form">
    <input type="hidden" name="formType" value="edit">
    <input type = "hidden" name="table_model" value="'.$table.'">
    <input type = "hidden" name="id" value="'.$id.'">
                <div class="modal-body">
                <div class="row">';
    foreach ($namesAndTypes as $faKey => $field) {
        if($field["Field"] == "id" || $field["Field"] == "fecha") continue;
        if($field["Null"] == "NO"){
            $required = "required";
        }else{
            $required ="";
        }
        if($field["Field"]== "imagen"){
            $form .= '<div class="form-group col-12">';
        }else{
            $form .= '<div class="form-group col-6">';
        }
        
        if($field["Key"] ==="MUL" ){
            $form.='<label>'.explode("_", $field["Field"])[0].'</label>
            <select '.$required.' name="'.htmlspecialchars($field["Field"]).'" class="form-control">';
            $selectOptions = getOptions($field["Field"]);
            foreach ($selectOptions as $faKey2 => $option) {
                if($register[$field["Field"]] == $option[0]){
                    $form.='<option selected value="'.htmlspecialchars($option[0]).'">'.htmlspecialchars($option[1]).'</option>';
                }else{
                    $form.='<option value="'.htmlspecialchars($option[0]).'">'.htmlspecialchars($option[1]).'</option>';
                }
            }
            $form.="</select>";
        }
        else{
            if(strpos($field["Type"], 'varchar') !== false){
                $type="text";
                if($field["Field"]=="email" || $field["Field"]=="password"){
                    $type=$field["Field"];
                }
            }else{
                $type ="number";
            }
            
            switch ($field["Field"]) {
                case 'imagen':
                $form.='<label for="field-'.htmlspecialchars($field["Field"]).'">'.htmlspecialchars($field["Field"]).'</label>';
                $form.='<div class="input-group">
                <div class="custom-file">
                <input value="'.$register[$field["Field"]].'" '.htmlspecialchars($required).' type="file" name="'.htmlspecialchars($field["Field"]).'" class="custom-file-input" id="'.$field["Field"].'">
                <label class="custom-file-label" for="'.$field["Field"].'">Foto</label>
                </div>
                </div>';
                    break;
                case 'rol':
                $form.='<label>'.htmlspecialchars($field["Field"]).'</label>
                <select '.$required.' name="'.htmlspecialchars($field["Field"]).'" class="form-control">';
                $form.='<option ';
                    if($register[$field["Field"]]==0){
                        $form.="selected "; 
                    }
                    $form.='value="0">estudiante</option>';
                    $form.='<option ';
                    if($register[$field["Field"]]==1){
                        $form.="selected "; 
                    }
                    $form.='value="1">administrativo</option>';
                $form.="</select>";
                    break;
                case 'estado':
                    $form.='<label>'.htmlspecialchars($field["Field"]).'</label>
                    <select '.$required.' name="'.htmlspecialchars($field["Field"]).'" class="form-control">';
                    $form.='<option ';
                    if($register[$field["Field"]]==0){
                        $form.="selected "; 
                    }
                    $form.='value="0">disponible</option>';
                    $form.='<option ';
                    if($register[$field["Field"]]==1){
                        $form.="selected "; 
                    }
                    $form.='value="1">no disponible</option>';
                    $form.="</select>";
                    break;
                case 'accion':
                    $form.='<label>'.htmlspecialchars($field["Field"]).'</label>
                    <select '.$required.' name="'.htmlspecialchars($field["Field"]).'" class="form-control">';
                    $form.='<option ';
                    if($register[$field["Field"]]==0){
                        $form.="selected "; 
                    }
                    $form.='value="0">devolución</option>';
                    $form.='<option ';
                    if($register[$field["Field"]]==1){
                        $form.="selected "; 
                    }
                    $form.='value="1">arrendamiento</option>';
                    $form.="</select>";
                    break;
                default:
                $form.='<label for="field-'.htmlspecialchars($field["Field"]).'">'.htmlspecialchars($field["Field"]).'</label>';
                $form.='<input value="'.$register[$field["Field"]].'" '.htmlspecialchars($required).' type="'.htmlspecialchars($type).'" name="'.htmlspecialchars($field["Field"]).'" class="form-control" placeholder="Enter '.htmlspecialchars($field["Field"]).'">';
                    break;
            }
            

            if($field["Field"]=="imagen"){
                
            }else{
                
            }
       }
       $form.="</div>";
    }
    $form.='</div>
        <div class="modal-footer">
        <button type="submit" id="submitButton" class="btn btn-primary">Submit</button>
    </div>
    </form>';

    return $form;
    
}
function getRegister($table, $id){
    $pdo = $GLOBALS["pdo"];
    $stmt = $pdo->prepare('SELECT * FROM '.$table.' where id = '.$id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function editRegister(){
    if(isset($_FILES['imagen'])){
        $info = pathinfo($_FILES['imagen']['name']);
        $ext = $info['extension']; // get the extension of the file
        $newname = "user".$_POST["users_id"]."_articulo".$_POST["articulo_id"]."_".time().".".$ext; 
        $_POST["imagen"]=$newname;
        $target = 'images/'.$newname;
        move_uploaded_file( $_FILES['imagen']['tmp_name'], $target);
    }
    $table = $_POST["table_model"];
    $id = $_POST["id"];
    $cols = getColumnNames($table);
    $colsAndValues = array();
    foreach ($cols as $faKey => $colName) {
        if(isset($_POST[$colName])){
            array_push($colsAndValues, array("col" => $colName, "val" => $_POST[$colName]));
        }
    }
    $setQuery = "";
    foreach ($colsAndValues as $faKey2 => $combo) {
        $setQuery.=$combo["col"]." = :".$combo["col"].", ";
    }
    $setQuery = substr($setQuery, 0, -2);
    $pdo = $GLOBALS["pdo"];
    $stmt = $pdo->prepare('UPDATE '.$table.' SET '.$setQuery.' where id = '.$id);
    foreach ($colsAndValues as $faKey3 => $combo) {
        $stmt->bindValue(":".$combo["col"], $combo["val"]);
    }
    try{
        $stmt->execute();
        if ($table == "historial"){
            return changeEstadoArticulo($_POST["articulo_id"], $_POST["accion"]);
        }else{
            return "Registro editado correctamente";
        }
    } catch (PDOException $e) {
        return "Error editado registro";
    }
}
function userHistorialForm($table, $articulo_id, $user_id, $accion){
    if(isset($articulo_id)){
        $articulo_name = getNameFromId("articulo", $articulo_id);
        $modalTitle = "Arrendar $articulo_name";
    }else{
        $modalTitle = "Devolver artículo";
    }
    $namesAndTypes = getColumnSpecs($table);
    $onlyFields=array("imagen");
    $form= '<div class="modal-header">
                <h5 class="modal-title" id="formModalTitle">'.$modalTitle.'</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
    <form id="modalForm" action="routes.php" enctype="multipart/form-data" method="POST" role="form">
    <input type="hidden" name="formType" value="insert">
    <input type = "hidden" name="table_model" value="'.$table.'">
    <input type = "hidden" name="users_id" value="'.$user_id.'">';
    if(isset($articulo_id)){
        $form.='<input type = "hidden" name="articulo_id" value="'.$articulo_id.'">';
    }else{
        array_push($onlyFields, "articulo_id");
    }
    $form.='<input type = "hidden" name="accion" value="'.$accion.'">
                <div class="modal-body">
                <div class="row">';
    foreach ($namesAndTypes as $faKey => $field) {
        if(!in_array($field["Field"], $onlyFields)) continue;
        if($field["Null"] == "NO"){
            $required = "required";
        }else{
            $required ="";
        }
        if($field["Field"]== "imagen"){
            $form .= '<div class="form-group col-12">';
        }else{
            $form .= '<div class="form-group col-6">';
        }
        
        if($field["Key"] ==="MUL" ){
            $form.='<label>'.explode("_", $field["Field"])[0].'</label>
            <select '.$required.' name="'.htmlspecialchars($field["Field"]).'" class="form-control">';
            if($field["Field"] == "articulo_id"){
                $selectOptions = getSpecificOptions($table, $field["Field"], "users_id", $user_id);
            }else{
                $selectOptions = getOptions($field["Field"]);
            }
            foreach ($selectOptions as $faKey2 => $option) {

                $form.='<option value="'.htmlspecialchars($option).'">'.htmlspecialchars(getNameFromId(explode("_", $field["Field"])[0], $option)).'</option>';
            }
            $form.="</select>";
        }
        else{
            if(strpos($field["Type"], 'varchar') !== false){
                $type="text";
                if($field["Field"]=="email" || $field["Field"]=="password"){
                    $type=$field["Field"];
                }
            }else{
                $type ="number";
            }
            
            switch ($field["Field"]) {
                case 'imagen':
                $form.='<label for="field-'.htmlspecialchars($field["Field"]).'">'.htmlspecialchars($field["Field"]).'</label>';
                $form.='<div class="input-group">
                <div class="custom-file">
                <input '.htmlspecialchars($required).' type="file" name="'.htmlspecialchars($field["Field"]).'" class="custom-file-input" id="'.$field["Field"].'">
                <label class="custom-file-label" for="'.$field["Field"].'">Foto</label>
                </div>
                </div>';
                    break;
                default:
                    break;
            }
       }
       $form.="</div>";
    }
    $form.='</div>
        <div class="modal-footer">
        <button type="submit" id="submitButton" class="btn btn-primary">Submit</button>
    </div>
    </form>';

    return $form;
    
}
function getSpecificOptions($table, $wantedField, $referenceField, $referenceValue){
    $pdo = $GLOBALS["pdo"];
    $stmt = $pdo->prepare('SELECT DISTINCT '.$wantedField.' FROM '.$table.' where '.$referenceField.' = '.$referenceValue);
    $stmt->execute();
    $options = array();
    while($row = $stmt->fetch(PDO::FETCH_NUM)){
        array_push($options, $row[0]);
    }
    $id_list ="";
    foreach ($options as $faKey => $option_id) {
        $id_list.="$option_id, ";
    }
    $id_list = substr($id_list, 0, -2);
    $wantedTable =  explode("_", $wantedField)[0];
    $stmt = $pdo->prepare('SELECT id FROM '.$wantedTable.' where id in ('.$id_list.') and estado = 1');
    $stmt->execute();
    $options = array();
    while($row = $stmt->fetch(PDO::FETCH_NUM)){
        array_push($options, $row[0]);
    }
    return $options;
}
?>