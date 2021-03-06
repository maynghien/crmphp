<?php
    class Customer extends Controller{
        function getQueryParam($name){
            $url=$_SERVER["REQUEST_URI"];
            $url= explode("?", $url)[1];
            $arr=explode("&", $url);
            foreach ($arr as $pairstr) {
                $pair=explode("=", $pairstr);
                if($pair[0]==$name){
                    return $pair[1];
                }
            }
            return "";
        }
        function Index() {
            //header('Location: PanelAdmin/ViewData');
            if(!isset($_SESSION['role'])) die;
            
            $role=$_SESSION['role'];
            $Account = $this->model("AccountModel");
            $NhanVienList = mysqli_fetch_all($Account->GetAllAccount());
            $user = $Account->GetUserID($_SESSION['username']);
            if($row = mysqli_fetch_array($user)) {
                $curentuserid = $row["userid"];
            }
            $query="WHERE 1 ";
            $userid="";
            if($role=="nhanvien")
                $userid=$curentuserid;
            else{
                $quserid=$this->getQueryParam("userid");
                if($quserid!="")
                    $userid=$quserid;
                else
                    $userid='all';
            
            }
            if($userid!="all"){
                $query.="and userid='".$userid."'";
            }
            $trangthai=$this->getQueryParam("trangthai");
            $cmnd=$this->getQueryParam("cmnd");
            if($cmnd != "") $query.=" and cmnd='${cmnd}'";
            $sodt=$this->getQueryParam("sodt");
            if($sodt != "") $query.=" and cmnd='${sodt}'";
            $ngaybd=$this->getQueryParam('ngaybd');

            $ngaykt=$this->getQueryParam('Ngaykt');
            $today = date("Y-m-d");
            if($ngaybd == "") $query.=" and ngaythem >= '${today}'";
            if($ngaybd == "") $query.=" and ngaythem >= '${today}'";
            else $qr.=" and ngaythem >= '${ngaybd}'";
            if($ngaykt == "") $query.=" and ngaythem <= '${today}'";
            else if($ngaykt != "") $query.=" and ngaythem <= '${ngaykt}'";
            else if($ngaybd == "" && $ngaykt == "") $query.=" and ngaythem ='${today}'";

            $page=$this->getQueryParam("page");
            $limit=$this->getQueryParam("limit");
            $totalrows= mysqli_fetch_all($Customer->Query("SELECT count(customerid) from CRM_customers WHERE ".$query));
            $data = mysqli_fetch_all($Customer->Query("SELECT * from CRM_customers WHERE ".$query));
            echo $totalrows;
            $view = $this->view("LayoutBinh", __CLASS__, [
                "View" => "Index",
                "Controller"=> "Customer",
                "Trangthai"=>$trangthai,
                "Cmnd"=>$cmnd,
                "Sodt"=>$sodt,
                "Ngaybd"=>$ngaybd,
                "Ngaykt"=>$ngaykt,

                "Userid"=>$userid,
                "NhanVienList" => $NhanVienList,
                "page"=>$page,
                "limit"=>$limit,
                "Totalrows"=>$totalrows,
            ]);
            echo $view;
        }
        function InsertData() {
            if(!isset($_SESSION['role'])) die;
            if($_SESSION['role'] != "admin") die;
                $Account = $this->model("AccountModel");
                $data = mysqli_fetch_all($Account->GetNhanVien());
            if($_SESSION['role'] == "admin")
                $view = $this->view("Layout1",__CLASS__, [
                    "Page" => "adminpanel",
                    "Nhanvien" => $data,
                ]);
            else {
                $view = $this->view("Layout1", __CLASS__, [
                    "Page" => "home",
                ]);
            }
            echo $view;
        }
        
        function ViewData($page = 1, $limit = 10) {
            if(!isset($_SESSION['role'])) die;
            if($_SESSION['role'] != "admin") die;
            $Customer = $this->model("CustomerModel");
            $Account = $this->model("AccountModel");
            // GET nut lo??c 
            $trangthai = "";
            $hoten = "";
            $cmnd = "";
            $sodt = "";
            $userid = "";
            $ngaybd = "";
            $ngaykt = "";
            $qr="";
            $page1=1;
            $data = (explode('=',urldecode($_SERVER['REQUEST_URI'])));
            if(isset($data[1])) {
                $trangthai = substr($data[1],0,-6);
                if($trangthai=="all"){
                     $qr = "SELECT * FROM CRM_customers where 1=1";
                }
                else if($trangthai=='new'){
                    $qr = "SELECT * FROM CRM_customers where trangthai is null";
                }
                else{
                 $qr = "SELECT * FROM CRM_customers where trangthai ='$trangthai'";
                }
            }

            else{
                $qr = "SELECT * FROM CRM_customers where 1=1";
            }
            if(isset($data[2])) {
                $hoten = substr($data[2],0,-5);
                if($hoten != "") $qr.=" and hoten LIKE '%${hoten}%'";
            }
            if(isset($data[3])) {
                $cmnd = substr($data[3],0,-5);
                if($cmnd != "") $qr.=" and cmnd='${cmnd}'";
            }
            if(isset($data[4])) {
                $sodt = substr($data[4],0,-7);
                if($sodt != "") $qr.=" and cmnd='${sodt}'";
            }
            if(isset($data[5])) {
                $userid = substr($data[5],0,-7);
                if($userid != "all") $qr.=" and userid=${userid}";
            }
            if(isset($data[6])) {
                $ngaybd = substr($data[6],0,-7);
            }
            if(isset($data[7])) {
                $ngaykt = $data[7];
            }
            if(isset($data[8])) {
                $ngaykt = substr($ngaykt, 0, -5);
                $page1 = $data[8];
            }
            $today = date("Y-m-d");
            if($ngaybd == "") $qr.=" and ngaythem >= '${today}'";
            else $qr.=" and ngaythem >= '${ngaybd}'";
            if($ngaykt == "") $qr.=" and ngaythem <= '${today}'";
            else if($ngaykt != "") $qr.=" and ngaythem <= '${ngaykt}'";
            else if($ngaybd == "" && $ngaykt == "") $qr.=" and ngaythem ='${today}'";
            $min = 0;
            $min=($page1-1) * $limit;
            $qr1 = $qr;
            $qr.=" LIMIT ${min}, ${limit}";
            echo $qr;
            // if($trangthai == "all" && $userid == "all") {
            //     if($cmnd == "" && $sodt == "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM `CRM_customers` WHERE ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'  LIMIT ${min}, ${limit}";
            //         }
            //     }
            //     else if($cmnd != "" && $sodt == "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where cmnd='${cmnd}' and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM CRM_customers where cmnd='${cmnd}' and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            //     else if($cmnd == "" && $sodt != "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where sodt='${sodt}' and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM CRM_customers where sodt='${sodt}' and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            //     else if($cmnd != "" && $sodt != "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where sodt='${sodt}' and cmnd='${cmnd}' and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM CRM_customers where sodt='${sodt}' and cmnd='${cmnd}' and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            // }
            // else if($trangthai != "all" && $userid == "all") {
            //     if($cmnd == "" && $sodt == "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where trangthai='${trangthai}' and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM `CRM_customers` WHERE trangthai='${trangthai}' and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            //     else if($cmnd != "" && $sodt == "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where trangthai='${trangthai}' and cmnd='${cmnd}' and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM CRM_customers where trangthai='${trangthai}' and cmnd='${cmnd}' and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            //     else if($cmnd == "" && $sodt != "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where trangthai='${trangthai}' and sodt='${sodt}' and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM CRM_customers where trangthai='${trangthai}' and sodt='${sodt}' and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            //     else if($cmnd != "" && $sodt != "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where trangthai='${trangthai}' and sodt='${sodt}' and cmnd='${cmnd}' and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM CRM_customers where trangthai='${trangthai}' and sodt='${sodt}' and cmnd='${cmnd}' and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            // }
            // else if($trangthai == "all" && $userid != "all") {
            //     if($cmnd == "" && $sodt == "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where userid=${userid} and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM `CRM_customers` WHERE userid=${userid} and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            //     else if($cmnd != "" && $sodt == "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where userid=${userid} and cmnd='${cmnd}' and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM CRM_customers where userid=${userid} and cmnd='${cmnd}' and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            //     else if($cmnd == "" && $sodt != "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where userid=${userid} and sodt='${sodt}' and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM CRM_customers where userid=${userid} and sodt='${sodt}' and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            // }
            // else if($trangthai != "all" && $userid != "all") {
            //     if($cmnd == "" && $sodt == "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where trangthai='${trangthai}' and userid=${userid} and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM `CRM_customers` WHERE trangthai='${trangthai}' and userid=${userid} and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            //     else if($cmnd != "" && $sodt == "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where trangthai='${trangthai}' and userid=${userid} and cmnd='${cmnd}' and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM CRM_customers where trangthai='${trangthai}' and userid=${userid} and cmnd='${cmnd}' and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            //     else if($cmnd == "" && $sodt != "") {
            //         if($ngaybd == "" || $ngaykt == "") {
            //             $today = date("Y-m-d");
            //             $qr = "SELECT * FROM CRM_customers where trangthai='${trangthai}' and userid=${userid} and sodt='${sodt}' and ngaythem='${today}'";
            //         }
            //         else {
            //             $qr="SELECT * FROM CRM_customers where trangthai='${trangthai}' and userid=${userid} and sodt='${sodt}' and ngaythem >= '${ngaybd}' and ngaythem <= '${ngaykt}'";
            //         }
            //     }
            // }
           if($trangthai != "") {
                $page1 = intval($page1);
                if($page != 1) header("Location: /PanelAdmin/ViewData?trangthai=${trangthai}&cmnd=${cmnd}&sodt=${sodt}&userid=${userid}&ngaybd=${ngaybd}&ngaykt=${ngaykt}");
                $data1 = mysqli_fetch_all($Account->GetNhanVien());
                $data = mysqli_fetch_all($Customer->Query($qr));
                $rows = mysqli_fetch_array($Customer->Query(str_replace('SELECT *','SELECT count(customerid)', $qr1)));
                $rowsnum = $rows["count(customerid)"];
                $user = $Account->GetUserID($_SESSION['username']);
                if($row = mysqli_fetch_array($user)) {
                    $userid = $row["userid"];
                }
                $data3 = mysqli_fetch_all($Customer->GetCustomer($userid, $min, $limit));
                    $view = $this->view("Layout1",__CLASS__, [
                        "Page" => "staffdata",
                        "Nhanvien" => $data1,
                        "Customer" => $data,
                        "Customer1" => $data3,
                        "Numrows" => $rowsnum,
                        "Pagenum" => $page1,
                        "Trangthai" => $trangthai,
                        "Hoten" => $hoten,
                        "Cmnd" => $cmnd,
                        "Sodt" => $sodt,
                        "Userid" => $userid,
                        "Ngaybd" => $ngaybd,
                        "Ngaykt" => $ngaykt
                        ]);
                    echo $view;
                    die;
           }
            
            //if($_SERVER['REQUEST_METHOD'] == "POST") {
                //$Account = $this->model("AccountModel");
                //$Customer = $this->model("CustomerModel");
                // nu??t lo??c ????n
                // if(isset($_POST['trangthai'])) {
                //     $trangthai = $_POST['trangthai'];
                //     $hoten = $_POST['hoten'];
                //     $cmnd = $_POST['cmnd'];
                //     $sodt = $_POST['sodt'];
                //     // userid la?? nh??n vi??n mu????n xem
                //     $userid = $_POST['userid'];
                //     $ngaybd = $_POST['ngaybd'];
                //     $ngaykt = $_POST['ngaykt'];
                //     $data1 = mysqli_fetch_all($Account->GetNhanVien());
                //     // error
                //     $rowsnum=0;
                //     $data2 = $Customer->GetCountSearch($trangthai, $hoten, $cmnd, $sodt, $userid, $ngaybd, $ngaykt);
                //     $rows = mysqli_fetch_array($data2);
                //     $rowsnum = $rows["count(customerid)"];
                //     $min = 0;
                //     $min=($page-1) * $limit;
                //     $data = mysqli_fetch_all($Customer->SearchCustomer($trangthai, $hoten, $cmnd, $sodt, $userid, $ngaybd, $ngaykt, $min, $limit));
                //     $view = $this->view("Layout1",__CLASS__, [
                //         "Page" => "staffdata",
                //         "Nhanvien" => $data1,
                //         "Customer" => $data,
                //         "Numrows" => $rowsnum,
                //         "Pagenum" => $page
                //         ]);
                //     echo $view;
                // }
            //}

            // GET
            $Customer = $this->model("CustomerModel");
            $min = 0;
            $min=($page-1) * $limit;
           $data = mysqli_fetch_all($Customer->Query($qr));
            $Account = $this->model("AccountModel");
            $data1 = mysqli_fetch_all($Account->GetNhanVien());
            $data2 = $Customer->GetCountCustomer();
            $rowsnum = 0;

            $rows = mysqli_fetch_array($data2);

            $user = $Account->GetUserID($_SESSION['username']);
            if($row = mysqli_fetch_array($user)) {
                $userid = $row["userid"];
            }
            $data3 = mysqli_fetch_all($Customer->GetCustomer($userid, $min, $limit));
            $rows = mysqli_fetch_array($data2);

            $rowsnum = $rows["count(customerid)"];
            $view = $this->view("Layout1",__CLASS__, [
                "Page" => "staffdata",
                "Customer" => $data,
                "Customer1" => $data3,
                "Nhanvien" => $data1,
                "Numrows" => $rowsnum,
                "Pagenum" => $page
                ]);
            echo $view;
            // END GET

        }
        // ??oa??n na??y cu??
        // function ViewData($page = 1, $limit = 10 ) {
        //     if(!isset($_SESSION['role'])) die;
        //     if($_SESSION['role'] != "admin") die;
        //     if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['uid']) and isset($_POST['customerIds'])) {
        //         $Customer = $this->model("CustomerModel");
        //         $userid = $_POST['uid'];
        //         foreach($_POST['customerIds'] as $customerid) {
        //             $Customer->UpdateUseridInCustomer($userid, $customerid);
        //         }
        //         echo "successfuly";
        //         die;
        //     }
        //     if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['trangthai'])) {
        //         $url = $_SERVER['REQUEST_URI'];
        //         $userid = substr(parse_url($url, PHP_URL_QUERY),7);
        //         $trangthai = $_POST['trangthai'];
        //         if($userid != null) {
        //             if($trangthai == "T???t c???") {
        //                 $Customer = $this->model("CustomerModel");
        //                 $data = mysqli_fetch_all($Customer->GetCustomer1($userid));
        //                 $Account = $this->model("AccountModel");
        //                 $data1 = mysqli_fetch_all($Account->GetNhanVien1($userid));
        //                 $view = $this->view("Layout1",__CLASS__, [
        //                     "Page" => "staffdata",
        //                     "Customer" => $data,
        //                     "Nhanvien" => $data1
        //                 ]);
        //                 echo $view;
        //                 die;
        //             }
        //             $Customer = $this->model("CustomerModel");
        //             $data = mysqli_fetch_all($Customer->GetCustomer2($userid, $trangthai));
        //             $Account = $this->model("AccountModel");
        //             $data1 = mysqli_fetch_all($Account->GetNhanVien1($userid));
        //             $view = $this->view("Layout1",__CLASS__, [
        //                 "Page" => "staffdata",
        //                 "Customer" => $data,
        //                 "Nhanvien" => $data1
        //             ]);
        //             echo $view;
        //         }
        //         else {
        //             if($trangthai == "T???t c???") {
        //                 $Customer = $this->model("CustomerModel");
        //                 $data = mysqli_fetch_all($Customer->GetAllCustomer($page, $limit));
        //                 $Account = $this->model("AccountModel");
        //                 $data1 = mysqli_fetch_all($Account->GetNhanVien1($userid));
                        
        //                 $view = $this->view("Layout1",__CLASS__, [
        //                     "Page" => "staffdata",
        //                     "Customer" => $data,
        //                     "Nhanvien" => $data1,
        //                 ]);
        //                 echo $view;
        //                 die;
        //             }
        //             $Customer = $this->model("CustomerModel");
        //             $data = mysqli_fetch_all($Customer->GetCustomer4($trangthai));
        //             $Account = $this->model("AccountModel");
        //             $data1 = mysqli_fetch_all($Account->GetNhanVien1($userid));
        //             $view = $this->view("Layout1",__CLASS__, [
        //                 "Page" => "staffdata",
        //                 "Customer" => $data,
        //                 "Nhanvien" => $data1
        //             ]);
        //             echo $view;
        //         }
        //     }
        //     // ch???n nv xem
        //     $url = $_SERVER['REQUEST_URI'];
        //     $userid = substr(parse_url($url, PHP_URL_QUERY),7);
        //     if($userid == null) {
        //         $Customer = $this->model("CustomerModel");
        //         // page = 1 limit = 10
        //         $max = 0;
        //         $min = 0;
        //         $max+=$limit*$page; //first: 10, sec: 20
        //         $min+=($page * $limit)-9; //first: 0, sec: 10
        //         // echo "min: ". $min. " max: " . $max;die;
        //         $data = mysqli_fetch_all($Customer->GetAllCustomer($min, $max));
        //         $Account = $this->model("AccountModel");
        //         $data1 = mysqli_fetch_all($Account->GetNhanVien1($userid));
        //         $data2 = $Customer->GetCountCustomer();
        //         $rows =0;
        //         while($row = mysqli_fetch_array($data2))
        //         {
        //             $rows = $row["count(customerid)"];
        //         }
        //         $view = $this->view("Layout1",__CLASS__, [
        //             "Page" => "staffdata",
        //             "Customer" => $data,
        //             "Nhanvien" => $data1,
        //             "Numrows" => $rows,
        //             "Pagenum" => $page
        //         ]);
        //         echo $view;
        //     }
        //     else {
        //         $Customer = $this->model("CustomerModel");
        //         $data = mysqli_fetch_all($Customer->GetCustomer1($userid));
        //         $Account = $this->model("AccountModel");
        //         $data1 = mysqli_fetch_all($Account->GetNhanVien1($userid));
        //         $view = $this->view("Layout1",__CLASS__, [
        //             "Page" => "staffdata",
        //             "Customer" => $data,
        //             "Nhanvien" => $data1,
        //         ]);
        //         echo $view;
        //     }
        // }
        function ChuyenData() {
            if(!isset($_SESSION['role'])) die;
            if($_SESSION['role'] != "admin") die;
            if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['uid']) and isset($_POST['customerIds'])) {
                $Customer = $this->model("CustomerModel");
                $userid = $_POST['uid'];
                foreach($_POST['customerIds'] as $customerid) {
                    $Customer->UpdateUseridInCustomer($userid, $customerid);
                }
                echo "successfuly";
            }
        }
        function ManageUser($userid=null) {
            if(!isset($_SESSION['role'])) die;
            if($_SESSION['role'] != "admin") die;
            $Account = $this->model("AccountModel");
            $url = $_SERVER['REQUEST_URI'];
            $role = substr(parse_url($url, PHP_URL_QUERY),5);
            if($userid != null) {
                $Account->UpdateRole($userid, $role);
            }
            $data = mysqli_fetch_all($Account->GetAllAccount());
            $view = $this->view("Layout1",__CLASS__, [
                "Page" => "manageuser",
                "Account" => $data
            ]);
            echo $view;
        }
    }
?>