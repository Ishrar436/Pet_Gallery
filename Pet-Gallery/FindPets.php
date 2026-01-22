<?php
include "../db/db.php";
include "../nav/NavBar.php";

$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$category = mysqli_real_escape_string($conn, $_GET['category'] ?? '');

$sql = "SELECT p.*, s.shelter_name 
        FROM pets p 
        LEFT JOIN shelter s ON p.ShelterId = s.shelter_id 
        WHERE p.adoption_status = 'Available' 
        AND p.adminApproved
 = 1";


if (!empty($category)) {
$sql .= " AND LOWER(p.category) = LOWER('$category')";

}


if (!empty($search)) {
    $sql .= " AND (
        p.pet_name LIKE '%$search%' OR 
        p.breed LIKE '%$search%' OR 
        s.shelter_name LIKE '%$search%'
    )";
}


$sql .= " ORDER BY p.id DESC";

$result = mysqli_query($conn, $sql);


if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find a Pet</title>
    <link rel="stylesheet" href="../Pet-Gallery/FindPets.css">
    <link rel="stylesheet" href="../nav/NavBar.css">
</head>
<body>

 
<div class="find-container">
    <header class="filter-section">
        <form method="GET" action="findPets.php" class="filter-form">
            
            <div class="search-box">
                <input type="text" name="search" placeholder="Search breed or name..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">🔍</button>
            </div>

            <div class="category-filters">
                <label class="radio-label">
                    <input type="radio" name="category" value="" onchange="this.form.submit()" <?php if($category == '') echo 'checked'; ?>>
                    <span>All</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="category" value="Dog" onchange="this.form.submit()" <?php if($category == 'Dog') echo 'checked'; ?>>
                    <span>Dogs</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="category" value="Cat" onchange="this.form.submit()" <?php if($category == 'Cat') echo 'checked'; ?>>
                    <span>Cats</span>
                </label>
            </div>

            <a href="findPets.php" class="reset-link">Clear</a>
        </form>
    </header>

    <section class="pet-display">
        <div class="grid" id="petGrid">
            <?php while($pet = mysqli_fetch_assoc($result)): ?>
             
                  <div class="card">
                        <div class="img-container">
                            <?php if(!empty($pet['petPhoto'])): ?>
                                <img src="<?php echo $pet['petPhoto']; ?>" alt="<?php echo htmlspecialchars($pet['pet_name']); ?>" class="main-img">
                            <?php endif; ?>
                        </div>
                    <div class="content">
                        <h4><?php echo $pet['petName']; ?></h4>
                        <span class="badge"><?php echo $pet['breed']; ?></span>
                        <p><?php echo $pet['age']; ?> • <?php echo $pet['color']; ?></p>
                        <button type="button" class="view-btn" 
                                onclick="window.location.href='../PetDetails/PetDetails.php?id=<?php echo $pet['id']; ?>'">
                            Quick View
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
</div>

<script src="../Pet-Gallery/FindPets.js"></script>
<script src="../nav/NavBar.js"></script>
</body>
</html>