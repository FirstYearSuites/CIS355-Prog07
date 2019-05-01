<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
class Club {
    public $id;
    public $clubName;
    public $members;
    public $statement;

    private $noerrors = true;
    private $clubNameError = null;
    private $statementError = null;
    private $membersError = null;

    private $title = "Club";
    private $tableName = "Clubs";
    private $urlName =  "clubs";
    public $pictureContent;
    public $fileName;
    public $tempFileName;
    public $fileSize;
    public $fileType;
    function create_record() { // display "create" form
        $this->generate_html_top (1);
        $this->generate_form_picture($this->pictureContent, "content", "create", "required");
        $this->generate_form_group("input","statement", $this->statementError, $this->statement, "required");
        $this->generate_form_group("input","clubName", $this->clubNameError, $this->clubName, "autofocus");
        $this->generate_form_group("input","members", $this->membersError, $this->members);
        $this->generate_html_bottom (1);
    } // end function create_record()

    function read_record($id) { // display "read" form
        $this->select_db_record($id);
        $this->generate_html_top(2);
        $this->generate_form_picture($this->pictureContent, "content", "read");
        $this->generate_form_group("input","statement", $this->statementError, $this->statement, "disabled");
        $this->generate_form_group("input","clubName", $this->clubNameError, $this->clubName, "disabled");
        $this->generate_form_group("input","members", $this->membersError, $this->members, "disabled");
        $this->generate_html_bottom(2);
    } // end function read_record()

    function update_record($id) { // display "update" form
        if($this->noerrors) $this->select_db_record($id);
        $this->generate_html_top(3, $id);
        $this->generate_form_picture($this->pictureContent, "content", "update");
        $this->generate_form_group("input","statement", $this->statementError, $this->statement, "required");
        $this->generate_form_group("input","clubName", $this->clubNameError, $this->clubName, "autofocus onfocus='this.select()'");
        $this->generate_form_group("input","members", $this->membersError, $this->members);
        $this->generate_html_bottom(3);
    } // end function update_record()

    function delete_record($id) { // display "read" form
        $this->select_db_record($id);
        $this->generate_html_top(4, $id);
        $this->generate_form_picture($this->pictureContent, "content", "delete");
        $this->generate_form_group("input","statement", $this->statementError, $this->statement, "disabled");
        $this->generate_form_group("input","clubName", $this->clubNameError, $this->clubName, "disabled");
        $this->generate_form_group("input","members", $this->membersError, $this->members, "disabled");
        $this->generate_html_bottom(4);
    }

    function insert_db_record () {
        $fp      = fopen($this->tempFileName, 'r');
        $content = fread($fp, filesize($this->tempFileName));
        fclose($fp);
        if ($this->fieldsAllValid ()) { // validate user input
            $pdo = Database::connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO $this->tableName (clubName, members, statement, filename, filetype, content, filesize) values(?, ?, ?, ?, ?, ?, ?)";
            $q = $pdo->prepare($sql);
            $q->execute(array($this->clubName, $this->members, $this->statement, $this->fileName, $this->fileType, $content, $this->fileSize));
            $this->id = $pdo->lastInsertId();
            $absolutePath = $this->store_file_locally();
            $sql = "UPDATE $this->tableName  set absolutepath = ? WHERE id = ?";
            $q = $pdo->prepare($sql);
            $q->execute(array($absolutePath, $this->id));
			Database::disconnect();
            header("Location: $this->urlName.php"); // go back to "list"
        }
        else {
            $this->create_record();
        }
    } 
	
    private function select_db_record($id) {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM $this->tableName where id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        $data = $q->fetch(PDO::FETCH_ASSOC);
        Database::disconnect();
        $this->clubName = $data['clubName'];
        $this->members = $data['members'];
		$this->statement = $data['statement'];
        $this->pictureContent = $data['content'];
    } 

    function update_db_record ($id) {
        if ($this->tempFileName != null) {
            $fp = fopen($this->tempFileName, 'r');
            $content = fread($fp, filesize($this->tempFileName));
            fclose($fp);
            $this->id = $id;
            if ($this->fieldsAllValid()) {
                $this->noerrors = true;
				$absolutePath = $this->store_file_locally();
                $pdo = Database::connect();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = "UPDATE $this->tableName  set clubName = ?, members = ?, statement = ?, filename = ?, filetype = ?, content = ?, filesize = ?, absolutePath = ? WHERE id = ?";
                $q = $pdo->prepare($sql);
                $q->execute(array($this->clubName, $this->members, $this->statement, $this->fileName, $this->fileType, $content, $this->fileSize, $absolutePath, $this->id));
                Database::disconnect();
                header("Location: $this->urlName.php");
            } else {
                $this->noerrors = false;
                $this->update_record($id);
            }
        } else {
            $this->id = $id;
            if ($this->fieldsAllValid()) {
                $this->noerrors = true;
                $pdo = Database::connect();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = "UPDATE $this->tableName  set clubName = ?, members = ?, statement = ? WHERE id = ?";
                $q = $pdo->prepare($sql);
                $q->execute(array($this->clubName, $this->members, $this->statement, $this->id));
                Database::disconnect();
                header("Location: $this->urlName.php");
            } else {
                $this->noerrors = false;
                $this->update_record($id);  // go back to "update" form
            }
        }
    } 

    function delete_db_record($id) {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM $this->tableName WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        Database::disconnect();
        header("Location: $this->urlName.php");
    } 
    
	function store_file_locally(){
        $fileLocation = "uploads/" . $this->id ."/";
        $fileFullPath = $fileLocation . $this->fileName;
        if (!file_exists($fileLocation))
            mkdir ($fileLocation, 0777, true); // create subdirectory, if necessary
        else
            array_map('unlink', glob($fileLocation . "*"));
        move_uploaded_file($this->tempFileName, $fileFullPath);
        chmod($fileFullPath, 0777);
	return realpath($fileFullPath);
    }

    private function generate_html_top ($fun, $id=null) {
        switch ($fun) {
            case 1: // create
                $funWord = "Create"; $funNext = "insert_db_record";
                break;
            case 2: // read
                $funWord = "Read"; $funNext = "none";
                break;
            case 3: // update
                $funWord = "Update"; $funNext = "update_db_record&id=" . $id;
                break;
            case 4: // delete
                $funWord = "Delete"; $funNext = "delete_db_record&id=" . $id;
                break;
            default:
                echo "Error: Invalid function: generate_html_top()";
                exit();
                break;
        }
        echo "<!DOCTYPE html>
        <html>
            <head>
                <title>$funWord a $this->title</title>
                    ";
        echo "
                <meta charset='UTF-8'>
                <script src=\"https://code.jquery.com/jquery-3.3.1.min.js\"
                integrity=\"sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=\"
                crossorigin=\"anonymous\"></script>
                <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css' rel='stylesheet'>
                <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js'></script>
                <style>label {width: 5em;}</style>
                    ";
        echo "
            </head>";
        echo "
            <body>
                <div class='container'>
                    <div class='span10 offset1'>
                        <p class='row'>
                            <h3>$funWord a $this->title</h3>
                        </p>
                        <form class='form-horizontal' action='$this->urlName.php?fun=$funNext' method='post' enctype='multipart/form-data' onsubmit='return Validate(this);'>                        
                    ";
    } // end function generate_html_top()

    private function generate_html_bottom ($fun) {
        switch ($fun) {
            case 1: // create
                $funButton = "<button type='submit' class='btn btn-success'>Create</button>";
                break;
            case 2: // read
                $funButton = "";
                break;
            case 3: // update
                $funButton = "<button type='submit' class='btn btn-warning'>Update</button>";
                break;
            case 4: // delete
                $funButton = "<button type='submit' class='btn btn-danger'>Delete</button>";
                break;
            default:
                echo "Error: Invalid function: generate_html_bottom()";
                exit();
                break;
        }
        echo " 
                            <div class='form-actions'>
                                $funButton
                                <a class='btn btn-secondary' href='$this->urlName.php'>Back</a>
                            </div>
                        </form>
                    </div>
                </div> <!-- /container -->
            </body>
        </html>
        <script>
        // Code taken from https://canvas.svsu.edu/courses/28460/files/folder/_file_upload
            var _validFileExtensions = [\".jpg\", \".jpeg\", \".gif\", \".png\"];    
            function Validate(oForm) {
                var arrInputs = oForm.getElementsByTagName(\"input\");
                for (var i = 0; i < arrInputs.length; i++) {
                    var oInput = arrInputs[i];
                    if (oInput.type == \"file\") {
                        var sFileName = oInput.value;
                        if (sFileName.length > 0) {
                            var blnValid = false;
                            for (var j = 0; j < _validFileExtensions.length; j++) {
                                var sCurExtension = _validFileExtensions[j];
                                if (sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase() == sCurExtension.toLowerCase()) {
                                    blnValid = true;
                                    break;
                                }
                            }
                            
                            if (!blnValid) {
                                alert(\"Sorry, \" + sFileName + \" is invalid, allowed extensions are: \" + _validFileExtensions.join(\", \"));
                                return false;
                            }
                                                        
                        }
                    }
                }
                return true;
            }
        </script>
                    ";
    } // end function generate_html_bottom()

    private function generate_form_group ($node, $label, $labelError, $val, $modifier="") {
        echo "<div class='form-group'";
        echo !empty($labelError) ? ' alert alert-danger ' : '';
        echo "'>";
        echo "<label class='control-label'>$label &nbsp;</label>";
        //echo "<div class='controls'>";
        echo "<" . $node . " "
            . "name='$label' "
            . "type='text' "
            . "$modifier "
            . "placeholder='$label' "
            . "value='";
        echo !empty($val) ? $val : '';
        echo "'>";
        if (!empty($labelError)) {
            echo "<span class='help-inline'>";
            echo "&nbsp;&nbsp;" . $labelError;
            echo "</span>";
        }
        echo "</div>"; // end div: class='form-group'
    }
	
	
    private function generate_form_picture($content, $type, $action, $required="")
    {
        switch ($type){
            case "content"://in the case that it 
                echo '<img id=imgDisplay overflow=hidden width=200 height=200 src="data:image/jpeg;base64,' . base64_encode( $content ).'"/>';
                break;
            case "path":
                //echo '<img id=imgDisplay overflow=hidden width=200 height=20 src="data:image/jpeg;base64,' . base64_encode( $content ).'"/>';
                break;
        }
        switch ($action) {
            case "create":
            case "update":
                echo '<br><input type="file" name="Filename"' . $required . ' onchange="readURL(this);">
                        <script type="text/javascript">
                        function readURL(input) {
                            if (input.files[0].size > 1000000) {
                                input.value = null;
                                alert("The picture cannot be larger than 1MB in size!");
                            }
                            
                            if (input.files && input.files[0]) {
                                var reader = new FileReader();
                                reader.onload = function (e) {
                                    $(\'#imgDisplay\').attr(\'src\', e.target.result);
                                }
                
                                reader.readAsDataURL(input.files[0]);
                            } else {
                                    $(\'#imgDisplay\').attr(\'src\', null);
                            }
                        }
                        </script>';
                break;
        }
    }

    private function fieldsAllValid () {
        $valid = true;
        if (empty($this->clubName)) {
            $this->clubNameError = 'Please enter club name';
            $valid = false;
        }
        if (empty($this->statement)) {
            $this->statementError = 'Please enter a mission statement';
            $valid = false;
        }
        if (empty($this->members)) {
            $this->membersError = 'Please enter the number of members in the club';
            $valid = false;
        }
        return $valid;
    } // end function fieldsAllValid()
    function list_records() {
        echo "<!DOCTYPE html>
        <html>
            <head>
                <title>$this->title" . "s" . "</title>
                    ";
        echo "
                <meta charset='UTF-8'>
                <script src=\"https://code.jquery.com/jquery-3.3.1.min.js\"
                integrity=\"sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=\"
                crossorigin=\"anonymous\"></script>
                <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css' rel='stylesheet'>
                <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js'></script>
                    ";
        echo "
            </head>
            <body>
                <a href='https://github.com/'>GitHub</a><br />
                <a href='diagrams/Screenflow1.PNG'>Flow Diagram</a><br />
		<a href='uploads/' >Image Uploads</a><br />
                <div class='container'>
                    <p class='row'>
                        <h3>$this->title" . "s" . "</h3>
                    </p>
                    <p>
                        <a href='$this->urlName.php?fun=display_create_form' class='btn btn-success'>Create</a>
                    </p>
                    <div class='row'>
                        <table class='table table-striped table-bordered'>
                            <thead>
                                <tr>
                                    <th>Club Name</th>
                                    <th># of Members</th>
                                    <th>Mission Statement</th>
                                    <th>Club Profile Image</th>
                                    <th>Action</th>
                                </tr>

                            </thead>
                            <tbody>
                    ";
        $pdo = Database::connect();
        $sql = "SELECT * FROM $this->tableName ORDER BY id DESC";
        foreach ($pdo->query($sql) as $row) {
            echo "<tr>";
            echo "<td>". $row["clubName"] . "</td>";
            echo "<td>". $row["members"] . "</td>";
            echo "<td>". $row["statement"] . "</td>";
            echo "<td>" . '<img width=50 height=50 src="data:image/jpeg;base64,' . base64_encode( $row['content'] ).'"/>' . "</td>";
            echo "<td width=350>";
            echo "<a class='btn btn-info' href='$this->urlName.php?fun=display_read_form&id=".$row["id"]."'>Read</a>";
            echo "&nbsp;";
            echo "<a class='btn btn-warning' href='$this->urlName.php?fun=display_update_form&id=".$row["id"]."'>Update</a>";
            echo "&nbsp;";
            echo "<a class='btn btn-danger' href='$this->urlName.php?fun=display_delete_form&id=".$row["id"]."'>Delete</a>";
            echo "</td>";
            echo "</tr>";
        }
        Database::disconnect();
        echo "
                            </tbody>
                        </table>
                    </div>
			<p>
				<a href='logout.php' class='btn btn-danger'>Logout</a>
			</p>
                </div>
            </body>
        </html>
                    ";
    } 
}