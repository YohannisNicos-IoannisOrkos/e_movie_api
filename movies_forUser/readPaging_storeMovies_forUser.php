<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once '../config/core.php';
include_once '../config/paging.php';
include_once '../config/database.php';
include_once '../objects/movie.php';

// utilities
$pagingUtil = new PagingUtil();

// instantiate database and product object
$database = new Database();
$db = $database-> getConnection();


  $mGenre = isset($_GET['genre']) ? $_GET['genre'] : "all";
  if(empty($mGenre) ||  ($mGenre == "''"))   $mGenre = "all";
  $mSort = isset($_GET['sort']) ? $_GET['sort'] :"all";
  $mYear =  isset($_GET['year']) ? $_GET['year'] :"all";
  $mOrder = isset($_GET['order']) ? $_GET['order'] : 'DESC';
  $mType = isset($_GET['type']) ? $_GET['type'] : '';
  $mStoreId = isset($_GET['storeId']) ? $_GET['storeId'] : '';

  //enum('western', 'local', 'others')


  // initialize object
  $movies = new Movies($db);


    if(!empty($mStoreId)){

      // query products
      $stmt =  $movies->readPaging_storeMovies_forUser($from_record_num,$records_per_page,$mStoreId,$mType,$mGenre,$mSort,$mYear,$mOrder);
      $num =   $stmt->rowCount();

      // check if more than 0 record found
      if($num>0){

          // products array
          $movies_arr=array();
          $movies_arr["records"]=array();
          $movies_arr["paging"]=array();

          // retrieve our table contents
          // fetch() is faster than fetchAll()
          // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
              // extract row
              // this will make $row['name'] to
              // just $name only
              extract($row);

              $movie_one=array(
                "imdb_id"  => $imdb_id,
                "title" => $title,
                "type" => $type,
                "year" =>  $year,
                "released" =>   $released,
                "runtime" =>  html_entity_decode($runtime),
                "synopsis"  =>  $synopsis,
                "poster"  =>  $poster,
                "certification"  =>  $certification,
                "genres"  =>   explode(",",$genres)
              );

              array_push($movies_arr["records"], $movie_one);
          }


          // include paging
          $total_rows= $movies->readPagingcount_storeMovies_forUser($mStoreId,$mType,$mGenre,$mSort,$mYear,$mOrder);
          $page_url="{$home_url}movies_forUser/movie_listStoreByPaging.php?";
          $paging=$pagingUtil->getPaging($page, $total_rows, $records_per_page, $page_url);
          $movies_arr["paging"]=$paging;


          // set response code - 200 OK
          http_response_code(200);
          echo json_encode($movies_arr);
      }

      else{
        // set response code - 204 No Content
        http_response_code(204);
        // tell the user products does not exist
        echo json_encode(array("message" => "No products found."));
      }
    }
    else{
        // set response code - 404 Not found
        http_response_code(400);
        echo json_encode(array("message" => "Data is incomplete."));
    }
?>
