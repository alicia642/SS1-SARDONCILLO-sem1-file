<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <style>
            body {
                background: skyblue;
            }
        </style>
    </head>

    <body>
        <h1>GRADE CALCULATOR</h1>
    <form action="" method="POST">
        <label>1st Quarter Grade</label> <input type="number" name="firstquarter"><br>
        <label>2st Quarter Grade</label> <input type="number" name="secondquarter"><br>
        <label>3st Quarter Grade</label> <input type="number" name="thirdquarter"><br>
        <label>4st Quarter Grade</label> <input type="number" name="fourthquarter"><br>
        <input type="submit" name="submit">
    </form>

<?php
if (isset($_POST['submit'])) {

    $first_quarter =$_POST['firstquarter'];
    $second_quarter =$_POST['secondquarter'];
    $third_quarter =$_POST['thirdquarter'];
    $fourth_quarter =$_POST['fourthquarter'];

    $averageGrade = ($first_quarter + $second_quarter + $third_quarter + $fourthquarter);

if ($averageGrade >= 98 && $averageGrade <=96) {
   echo "Your average grade is: " . $averageGrade;
   echo "<br><br>OUTSTANDING!!!";

}elseif ($averageGrade >= 90 && $averageGrade <=94){
   echo "Your average grade is: " . $averageGrade;
   echo "<br><br>EXCELLENT!!!";

}elseif ($averageGrade >= 80 && $averageGrade <=84){
   echo "Your average grade is: " . $averageGrade;
   echo "<br><br>GOOD!!!";

}elseif ($averageGrade >= 75 && $averageGrade <=79){
   echo "Your average grade is: " . $averageGrade;
   echo "<br><br>NOT BAD!!!";

}else {
   echo "Your average grade is: " . $averageGrade;
   echo "<br><br>FAILED!!!";
}
}
   ?>
    </body>
</html>