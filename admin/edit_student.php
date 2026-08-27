<?php


require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/csrf.php";


function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}




$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id || $id <= 0) {

    header("Location: dashboard.php");
    exit();

}



$stmt = $pdo->prepare(
    "SELECT
        id,
        student_id,
        username,
        roll_number,
        department,
        academic_year,
        gender,
        phone,
        email,
        status
     FROM members
     WHERE id = ?
     LIMIT 1"
);

$stmt->execute([$id]);

$member = $stmt->fetch(PDO::FETCH_ASSOC);



if (!$member) {

    header("Location: dashboard.php");
    exit();

}


$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {



    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {

        http_response_code(403);

        exit("Invalid security token.");

    }


    

    $postId = filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );


    if (!$postId || $postId !== $id) {

        $errors[] = "Invalid member ID.";

    }


   

    $student_id = trim($_POST['student_id'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $roll_number = trim($_POST['roll_number'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = trim($_POST['status'] ?? 'Pending');



    if ($student_id === '') {
        $errors[] = "Student ID is required.";
    }

    if ($username === '') {
        $errors[] = "Name is required.";
    }

    if ($roll_number === '') {
        $errors[] = "Roll Number is required.";
    }

    if ($department === '') {
        $errors[] = "Department is required.";
    }

    if ($academic_year === '') {
        $errors[] = "Academic Year is required.";
    }

    if (!in_array(
        $gender,
        ['Male', 'Female', 'Other'],
        true
    )) {

        $errors[] = "Invalid gender.";

    }


    if ($phone === '') {
        $errors[] = "Phone number is required.";
    }


  

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Please enter a valid email address.";

    }



    $allowedStatuses = [
        'Pending',
        'Approved',
        'Rejected'
    ];


    if (!in_array(
        $status,
        $allowedStatuses,
        true
    )) {

        $errors[] = "Invalid membership status.";

    }



    if (empty($errors)) {

        $stmt = $pdo->prepare(
            "SELECT id
             FROM members
             WHERE student_id = ?
             AND id != ?
             LIMIT 1"
        );

        $stmt->execute([$student_id, $id]);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {

            $errors[] =
                "This Student ID already belongs to another member.";

        }

    }


 

    if (empty($errors)) {

        $stmt = $pdo->prepare(
            "SELECT id
             FROM members
             WHERE email = ?
             AND id != ?
             LIMIT 1"
        );

        $stmt->execute([$email, $id]);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {

            $errors[] =
                "This email already belongs to another member.";

        }

    }



    if (empty($errors)) {

        $stmt = $pdo->prepare(
            "UPDATE members
             SET
                student_id = ?,
                username = ?,
                roll_number = ?,
                department = ?,
                academic_year = ?,
                gender = ?,
                phone = ?,
                email = ?,
                status = ?
             WHERE id = ?"
        );


        if ($stmt->execute([
            $student_id,
            $username,
            $roll_number,
            $department,
            $academic_year,
            $gender,
            $phone,
            $email,
            $status,
            $id
        ])) {

            header("Location: dashboard.php");

            exit();

        } else {

            $errors[] =
                "Failed to update member.";

        }

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Edit Member - MTU Badminton Club
    </title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>


<body>


    <div class="container mt-5">

        <div class="card shadow">

            <div class="card-header">

                <h3 class="mb-0">
                    Edit Member
                </h3>

            </div>


            <div class="card-body">


                <!-- Errors -->

                <?php if (!empty($errors)): ?>

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        <?php foreach ($errors as $error): ?>

                        <li>
                            <?= e($error) ?>
                        </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

                <?php endif; ?>


                <!-- Edit Form -->

                <form method="POST" action="edit_student.php?id=<?= (int)$id ?>">


                    <!-- CSRF -->

                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">


                    <!-- ID -->

                    <input type="hidden" name="id" value="<?= (int)$id ?>">


                    <!-- Student ID -->

                    <div class="mb-3">

                        <label class="form-label">
                            Student ID
                        </label>

                        <input type="text" name="student_id" class="form-control" value="<?= e(
                            $_POST['student_id']
                            ?? $member['student_id']
                        ) ?>" required>

                    </div>


                    <!-- Name -->

                    <div class="mb-3">

                        <label class="form-label">
                            Name
                        </label>

                        <input type="text" name="username" class="form-control" value="<?= e(
                            $_POST['username']
                            ?? $member['username']
                        ) ?>" required>

                    </div>


                    <!-- Roll Number -->

                    <div class="mb-3">

                        <label class="form-label">
                            Roll Number
                        </label>

                        <input type="text" name="roll_number" class="form-control" value="<?= e(
                            $_POST['roll_number']
                            ?? $member['roll_number']
                        ) ?>" required>

                    </div>


                    <!-- Department -->

                    <div class="mb-3">

                        <label class="form-label">
                            Department
                        </label>

                        <input type="text" name="department" class="form-control" value="<?= e(
                            $_POST['department']
                            ?? $member['department']
                        ) ?>" required>

                    </div>


                    <!-- Academic Year -->

                    <div class="mb-3">

                        <label class="form-label">
                            Academic Year
                        </label>

                        <input type="text" name="academic_year" class="form-control" value="<?= e(
                            $_POST['academic_year']
                            ?? $member['academic_year']
                        ) ?>" required>

                    </div>


                    <!-- Gender -->

                    <div class="mb-3">

                        <label class="form-label">
                            Gender
                        </label>

                        <select name="gender" class="form-select" required>

                            <?php

                        $currentGender =
                            $_POST['gender']
                            ?? $member['gender'];

                        ?>


                            <option value="Male" <?= $currentGender === 'Male'
                                ? 'selected'
                                : '' ?>>
                                Male
                            </option>


                            <option value="Female" <?= $currentGender === 'Female'
                                ? 'selected'
                                : '' ?>>
                                Female
                            </option>


                            <option value="Other" <?= $currentGender === 'Other'
                                ? 'selected'
                                : '' ?>>
                                Other
                            </option>

                        </select>

                    </div>


                    <!-- Phone -->

                    <div class="mb-3">

                        <label class="form-label">
                            Phone
                        </label>

                        <input type="text" name="phone" class="form-control" value="<?= e(
                            $_POST['phone']
                            ?? $member['phone']
                        ) ?>" required>

                    </div>


                    <!-- Email -->

                    <div class="mb-3">

                        <label class="form-label">
                            Email
                        </label>

                        <input type="email" name="email" class="form-control" value="<?= e(
                            $_POST['email']
                            ?? $member['email']
                        ) ?>" required>

                    </div>


                    <!-- Status -->

                    <div class="mb-3">

                        <label class="form-label">
                            Membership Status
                        </label>


                        <?php

                    $currentStatus =
                        $_POST['status']
                        ?? $member['status'];

                    ?>


                        <select name="status" class="form-select" required>

                            <option value="Pending" <?= $currentStatus === 'Pending'
                                ? 'selected'
                                : '' ?>>
                                Pending
                            </option>


                            <option value="Approved" <?= $currentStatus === 'Approved'
                                ? 'selected'
                                : '' ?>>
                                Approved
                            </option>


                            <option value="Rejected" <?= $currentStatus === 'Rejected'
                                ? 'selected'
                                : '' ?>>
                                Rejected
                            </option>

                        </select>

                    </div>


                    <!-- Buttons -->

                    <div class="mt-4">

                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>


                        <a href="dashboard.php" class="btn btn-secondary">
                            Cancel
                        </a>

                    </div>


                </form>


            </div>

        </div>

    </div>


</body>

</html>
```