<?php

/**
 * =========================================================
 * MTU BADMINTON CLUB
 * SECURE MEMBER REGISTRATION
 * =========================================================
 */

session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

$message = "";


/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| Academic Year → Roll Number Prefix
|--------------------------------------------------------------------------
*/

$rollPrefixes = [

    '1st Year' => 'I.BE.',
    '2nd Year' => 'II.BE.',
    '3rd Year' => 'III.BE.',
    '4th Year' => 'IV.BE.',
    '5th Year' => 'V.BE.',
    '6th Year' => 'VI.BE.'

];


/*
|--------------------------------------------------------------------------
| Department → Roll Number Code
|--------------------------------------------------------------------------
*/

$departmentCodes = [

    'Civil Engineering' =>
        'CE',

    'Computer Engineering and Information Technology' =>
        'CEIT',

    'Electrical Power Engineering' =>
        'EP',

    'Electrical Engineering' =>
        'EC',

    'Chemical Engineering' =>
        'ChE',

    'Architectural Engineering' =>
        'Arch',

    'Mechanical Engineering' =>
        'ME',

    'Mining Engineering' =>
        'M',

    'Agricultural Engineering' =>
        'Argi',

    'Mechatronics Engineering' =>
        'MC',

    'Nuclear Engineering' =>
        'NC',

    'Biotechnology Engineering' =>
        'BioT'

];


/*
|--------------------------------------------------------------------------
| Department List
|--------------------------------------------------------------------------
*/

$departments = array_keys(
    $departmentCodes
);


/*
|--------------------------------------------------------------------------
| Academic Year List
|--------------------------------------------------------------------------
*/

$years = [

    '1st Year',
    '2nd Year',
    '3rd Year',
    '4th Year',
    '5th Year',
    '6th Year'

];


/*
|--------------------------------------------------------------------------
| Handle Registration
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | CSRF Verification
    |--------------------------------------------------------------------------
    */

    if (
        !verify_csrf_token(
            $_POST['csrf_token'] ?? null
        )
    ) {

        die("Invalid CSRF token.");
    }


    /*
    |--------------------------------------------------------------------------
    | Get Form Data
    |--------------------------------------------------------------------------
    */

    $username =
        trim(
            $_POST['username'] ?? ''
        );

    $student_id =
        trim(
            $_POST['student_id'] ?? ''
        );

    $roll_number =
        trim(
            $_POST['roll_number'] ?? ''
        );

    $department =
        trim(
            $_POST['department'] ?? ''
        );

    $academic_year =
        trim(
            $_POST['academic_year'] ?? ''
        );

    $gender =
        trim(
            $_POST['gender'] ?? ''
        );

    $phone =
        trim(
            $_POST['phone'] ?? ''
        );

    $email =
        strtolower(
            trim(
                $_POST['email'] ?? ''
            )
        );

    $password =
        $_POST['password'] ?? '';

    $confirm_password =
        $_POST['confirm_password'] ?? '';

    $terms =
        isset($_POST['terms']) &&
        $_POST['terms'] === 'on';

    $profile_picture = null;


    /*
    |--------------------------------------------------------------------------
    | Basic Validation
    |--------------------------------------------------------------------------
    */

    if (

        $username === '' ||
        $student_id === '' ||
        $roll_number === '' ||
        $department === '' ||
        $academic_year === '' ||
        $gender === '' ||
        $phone === '' ||
        $email === '' ||
        $password === '' ||
        $confirm_password === ''

    ) {

        $message = '

        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

            Please fill in all required fields.

        </div>';

    }


    /*
    |--------------------------------------------------------------------------
    | Terms Validation
    |--------------------------------------------------------------------------
    */

    elseif (!$terms) {

        $message = '

        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

            You must agree to the club code of conduct
            and safety guidelines.

        </div>';

    }


    /*
    |--------------------------------------------------------------------------
    | Student ID Validation
    |--------------------------------------------------------------------------
    |
    | Valid format:
    |
    | မနတ-XXXX
    |
    | Error message intentionally kept generic.
    |--------------------------------------------------------------------------
    */

    elseif (
        !preg_match(
            '/^မနတ-[\p{L}\p{N}-]+$/u',
            $student_id
        )
    ) {

        $message = '

        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

            Student ID is incorrect.

        </div>';

    }


    /*
    |--------------------------------------------------------------------------
    | Academic Year Validation
    |--------------------------------------------------------------------------
    */

    elseif (
        !isset(
            $rollPrefixes[$academic_year]
        )
    ) {

        $message = '

        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

            Invalid academic year selected.

        </div>';

    }


    /*
    |--------------------------------------------------------------------------
    | Department Validation
    |--------------------------------------------------------------------------
    */

    elseif (
        !isset(
            $departmentCodes[$department]
        )
    ) {

        $message = '

        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

            Invalid department selected.

        </div>';

    }


    /*
    |--------------------------------------------------------------------------
    | Roll Number Validation
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | 1st Year + CEIT + 2
    |
    | I.BE.CEIT-2
    |
    */

    elseif (
        !preg_match(
            '/^' .
            preg_quote(
                $rollPrefixes[$academic_year] .
                $departmentCodes[$department] .
                '-',
                '/'
            ) .
            '[0-9]{1,2}$/',
            $roll_number
        )
    ) {

        $message = '

        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

            Invalid roll number.

        </div>';

    }


    /*
    |--------------------------------------------------------------------------
    | Email Validation
    |--------------------------------------------------------------------------
    */

    elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message = '

        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

            Please enter a valid email address.

        </div>';

    }


    /*
    |--------------------------------------------------------------------------
    | Gmail Only
    |--------------------------------------------------------------------------
    */

    elseif (
        !preg_match(
            '/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
            $email
        )
    ) {

        $message = '

        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

            Please use a valid Gmail address ending with
            <strong>@gmail.com</strong>.

        </div>';

    }


    /*
    |--------------------------------------------------------------------------
    | Myanmar Phone Validation
    |--------------------------------------------------------------------------
    |
    | 09 + 9 digits
    |
    | Example:
    |
    | 09912345678
    |--------------------------------------------------------------------------
    */

    elseif (
        !preg_match(
            '/^09[0-9]{9}$/',
            $phone
        )
    ) {

        $message = '

        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

            Invalid Myanmar phone number.

            Please enter 09 followed by exactly 9 digits.

        </div>';

    }


    /*
    |--------------------------------------------------------------------------
    | Password Length
    |--------------------------------------------------------------------------
    */

    elseif (
        strlen($password) < 8
    ) {

        $message = '

        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

            Password must be at least 8 characters.

        </div>';

    }


    /*
    |--------------------------------------------------------------------------
    | Password Confirmation
    |--------------------------------------------------------------------------
    */

    elseif (
        $password !== $confirm_password
    ) {

        $message = '

        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

            Passwords do not match.

        </div>';

    }


    /*
    |--------------------------------------------------------------------------
    | Gender Validation
    |--------------------------------------------------------------------------
    */

    elseif (
        !in_array(
            $gender,
            [
                'Male',
                'Female',
                'Other'
            ],
            true
        )
    ) {

        $message = '

        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

            Invalid gender selected.

        </div>';

    }


    /*
    |--------------------------------------------------------------------------
    | Profile Picture Upload
    |--------------------------------------------------------------------------
    */

    if (

        $message === '' &&
        isset($_FILES['profile_picture']) &&
        $_FILES['profile_picture']['error'] !==
        UPLOAD_ERR_NO_FILE

    ) {

        $file =
            $_FILES['profile_picture'];


        /*
        |--------------------------------------------------------------------------
        | Upload Error
        |--------------------------------------------------------------------------
        */

        if (
            $file['error'] !==
            UPLOAD_ERR_OK
        ) {

            $message = '

            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                Profile picture upload failed.

            </div>';

        }


        /*
        |--------------------------------------------------------------------------
        | File Size
        |--------------------------------------------------------------------------
        */

        elseif (
            $file['size'] >
            2 * 1024 * 1024
        ) {

            $message = '

            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                Profile picture must be smaller than 2MB.

            </div>';

        }


        /*
        |--------------------------------------------------------------------------
        | Temporary File Validation
        |--------------------------------------------------------------------------
        */

        elseif (
            !is_uploaded_file(
                $file['tmp_name']
            )
        ) {

            $message = '

            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                Invalid uploaded file.

            </div>';

        }


        /*
        |--------------------------------------------------------------------------
        | MIME Type
        |--------------------------------------------------------------------------
        */

        else {

            $finfo =
                finfo_open(
                    FILEINFO_MIME_TYPE
                );

            $mime =
                finfo_file(
                    $finfo,
                    $file['tmp_name']
                );

            finfo_close(
                $finfo
            );


            $allowedTypes = [

                'image/jpeg' => 'jpg',
                'image/png'  => 'png'

            ];


            if (
                !isset(
                    $allowedTypes[$mime]
                )
            ) {

                $message = '

                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                    Only JPG and PNG images are allowed.

                </div>';

            } else {

                $uploadDir =
                    __DIR__ .
                    '/../uploads/profiles/';


                /*
                |--------------------------------------------------------------------------
                | Create Upload Directory
                |--------------------------------------------------------------------------
                */

                if (
                    !is_dir($uploadDir)
                ) {

                    if (
                        !mkdir(
                            $uploadDir,
                            0755,
                            true
                        )
                    ) {

                        $message = '

                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                            Could not create upload directory.

                        </div>';

                    }
                }


                if (
                    $message === ''
                ) {

                    $extension =
                        $allowedTypes[$mime];


                    try {

                        $fileName =
                            'profile_' .
                            bin2hex(
                                random_bytes(16)
                            ) .
                            '.' .
                            $extension;

                    } catch (
                        Throwable $e
                    ) {

                        $fileName = '';

                        $message = '

                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                            Could not generate secure filename.

                        </div>';

                    }


                    if (
                        $message === ''
                    ) {

                        $destination =
                            $uploadDir .
                            $fileName;


                        if (
                            move_uploaded_file(
                                $file['tmp_name'],
                                $destination
                            )
                        ) {

                            $profile_picture =
                                'uploads/profiles/' .
                                $fileName;

                        } else {

                            $message = '

                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                                Failed to save profile picture.

                            </div>';

                        }
                    }
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Information
    |--------------------------------------------------------------------------
    */

    $hashedPassword = '';

    if (
        $message === ''
    ) {

        $check =
            $pdo->prepare(
                "SELECT
                    id,
                    student_id,
                    email,
                    username
                 FROM members
                 WHERE student_id = ?
                    OR email = ?
                    OR username = ?
                 LIMIT 1"
            );


        if (!$check) {

            $message = '

            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                Database error.

            </div>';

        } else {

            $check->execute([

                $student_id,
                $email,
                $username

            ]);


            $existing =
                $check->fetch(
                    PDO::FETCH_ASSOC
                );


            if (
                $existing !== false
            ) {

                if (
                    $existing['student_id'] ===
                    $student_id
                ) {

                    $message = '

                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                        This Student ID is already registered.

                    </div>';

                } elseif (
                    strtolower(
                        $existing['email']
                    ) ===
                    strtolower($email)
                ) {

                    $message = '

                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                        This Gmail address is already registered.

                    </div>';

                } elseif (
                    $existing['username'] ===
                    $username
                ) {

                    $message = '

                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                        This username is already taken.

                    </div>';

                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Hash Password
    |--------------------------------------------------------------------------
    */

    if (
        $message === ''
    ) {

        $hashedPassword =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );


        if (
            $hashedPassword === false
        ) {

            $message = '

            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                Could not securely process password.

            </div>';

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Insert Member
    |--------------------------------------------------------------------------
    */

    if (
        $message === ''
    ) {

        $status = 'Pending';


        $stmt =
            $pdo->prepare(
                "INSERT INTO members
                (
                    username,
                    student_id,
                    roll_number,
                    department,
                    academic_year,
                    gender,
                    phone,
                    email,
                    password,
                    profile_picture,
                    status
                )
                VALUES
                (
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?
                )"
            );


        if (!$stmt) {

            $message = '

            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                Database error.

            </div>';

        } else {

            try {

                $success =
                    $stmt->execute([

                        $username,
                        $student_id,
                        $roll_number,
                        $department,
                        $academic_year,
                        $gender,
                        $phone,
                        $email,
                        $hashedPassword,
                        $profile_picture,
                        $status

                    ]);


                if ($success) {

                    $message = '

                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">

                        <strong>
                            Registration successful!
                        </strong>

                        <br>

                        Your membership is pending approval.

                    </div>';


                    $_POST = [];

                } else {

                    if (
                        $profile_picture !== null
                    ) {

                        $imagePath =
                            __DIR__ .
                            '/../' .
                            $profile_picture;


                        if (
                            is_file(
                                $imagePath
                            )
                        ) {

                            unlink(
                                $imagePath
                            );

                        }
                    }


                    $message = '

                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                        Registration failed.

                    </div>';

                }

            } catch (
                PDOException $e
            ) {

                if (
                    $profile_picture !== null
                ) {

                    $imagePath =
                        __DIR__ .
                        '/../' .
                        $profile_picture;


                    if (
                        is_file(
                            $imagePath
                        )
                    ) {

                        unlink(
                            $imagePath
                        );

                    }
                }


                if (
                    $e->getCode() === '23000'
                ) {

                    $message = '

                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                        Student ID, username, or Gmail address
                        already exists.

                    </div>';

                } else {

                    $message = '

                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">

                        Registration could not be completed.
                        Please try again.

                    </div>';

                }
            }
        }
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Register | MTU Badminton Club
    </title>


    <!-- Tailwind -->

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>


    <!-- Google Fonts -->

    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;600&display=swap"
        rel="stylesheet">


    <!-- Material Icons -->

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap"
        rel="stylesheet">


    <style>
    body {

        min-height:
            max(884px, 100dvh);

        font-family:
            'Inter',
            sans-serif;

    }


    h1,
    h2,
    h3 {

        font-family:
            'Montserrat',
            sans-serif;

    }


    .glass-card {

        background:
            rgba(255,
                255,
                255,
                0.75);

        backdrop-filter:
            blur(16px);

        -webkit-backdrop-filter:
            blur(16px);

        border:
            1px solid rgba(0,
                65,
                200,
                0.1);

    }


    .material-symbols-outlined {

        font-variation-settings:
            'FILL'0,
            'wght'400,
            'GRAD'0,
            'opsz'24;

    }
    </style>

</head>


<body class="bg-slate-50">


    <!-- Background -->

    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">

        <div class="absolute -top-24 -right-24
                   w-96 h-96
                   bg-blue-500/5
                   rounded-full
                   blur-3xl">
        </div>


        <div class="absolute top-1/2 -left-24
                   w-80 h-80
                   bg-blue-100/20
                   rounded-full
                   blur-3xl">
        </div>

    </div>


    <!-- Main -->

    <main class="relative z-10
               flex items-center justify-center
               min-h-screen
               px-4 py-8 md:py-24">

        <div class="w-full max-w-5xl
                   grid md:grid-cols-12
                   gap-6
                   items-start">


            <!-- LEFT SIDE -->

            <div class="hidden md:flex
                       md:col-span-4
                       flex-col gap-8
                       sticky top-24">

                <div class="flex items-center gap-3">

                    <span class="material-symbols-outlined
                               text-blue-700 text-4xl">

                        sports_tennis

                    </span>


                    <h1 class="font-bold
                               text-2xl
                               text-blue-700
                               tracking-tight">

                        Badminton Club

                    </h1>

                </div>


                <div>

                    <h2 class="font-bold
                               text-3xl
                               text-slate-900
                               mb-2">

                        Join the Court.

                    </h2>


                    <p class="text-slate-600">

                        Register to become an official
                        member of the MTU Badminton Club.

                    </p>

                </div>


                <div class="relative
                           w-full aspect-square
                           rounded-xl
                           overflow-hidden
                           glass-card
                           flex items-center
                           justify-center">

                    <img src="../assets/images/badminton.jpg" alt="Badminton"
                        class="w-full h-full object-cover opacity-90">

                </div>

            </div>


            <!-- FORM -->

            <div class="md:col-span-8 w-full">

                <div class="glass-card
                           rounded-xl
                           p-6 md:p-10
                           shadow-xl">


                    <!-- Header -->

                    <header class="mb-8">

                        <div class="md:hidden
                                   flex items-center
                                   gap-2 mb-3">

                            <span class="material-symbols-outlined
                                       text-blue-700">

                                sports_tennis

                            </span>


                            <span class="font-bold
                                       text-xl
                                       text-blue-700">

                                MTU Badminton Club

                            </span>

                        </div>


                        <h3 class="font-bold
                                   text-2xl md:text-3xl">

                            Member Registration

                        </h3>


                        <p class="text-slate-600
                                   text-sm
                                   mt-1">

                            Complete the details below to
                            secure your spot.

                        </p>

                    </header>


                    <!-- Message -->

                    <?= $message ?>


                    <!-- Registration Form -->

                    <form class="space-y-6" id="registrationForm" method="POST" action="register.php"
                        enctype="multipart/form-data">


                        <!-- CSRF -->

                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">


                        <!-- PROFILE PHOTO -->

                        <div class="flex flex-col
                                   items-center
                                   sm:flex-row
                                   gap-6
                                   pb-6
                                   border-b
                                   border-slate-200">

                            <div class="relative
                                       w-24 h-24
                                       rounded-full
                                       bg-slate-100
                                       flex items-center
                                       justify-center
                                       overflow-hidden
                                       border-2
                                       border-dashed
                                       border-blue-300" id="photoPreviewContainer">

                                <span class="material-symbols-outlined
                                           text-slate-400
                                           text-3xl" id="photoIcon">

                                    add_a_photo

                                </span>


                                <img class="hidden absolute inset-0
                                           w-full h-full
                                           object-cover" id="photoPreview" alt="Profile Preview">

                            </div>


                            <div class="flex-1
                                       text-center
                                       sm:text-left">

                                <label class="block
                                           font-semibold
                                           text-xs
                                           text-blue-700
                                           cursor-pointer
                                           hover:underline
                                           mb-1" for="profile_picture">

                                    Upload Profile Photo

                                </label>


                                <p class="text-slate-500
                                           text-[11px]
                                           leading-tight">

                                    JPG or PNG.
                                    Max size 2MB.
                                    <br>
                                    Used for your official
                                    club profile.

                                </p>


                                <input accept="image/jpeg,image/png" class="hidden" id="profile_picture"
                                    name="profile_picture" onchange="previewImage(this)" type="file">

                            </div>

                        </div>


                        <!-- FORM GRID -->

                        <div class="grid
                                   grid-cols-1
                                   md:grid-cols-2
                                   gap-5">


                            <!-- USERNAME -->

                            <div>

                                <label class="block text-xs
                                           font-semibold
                                           text-slate-600
                                           uppercase
                                           tracking-wider
                                           mb-2">

                                    Username

                                </label>


                                <input type="text" name="username" id="username" required maxlength="50"
                                    autocomplete="off" placeholder="Choose a username"
                                    value="<?= e($_POST['username'] ?? '') ?>" class="w-full
                                           bg-slate-100
                                           border
                                           border-slate-300
                                           rounded-lg
                                           px-4 py-3
                                           outline-none
                                           focus:ring-2
                                           focus:ring-blue-600">

                            </div>


                            <!-- STUDENT ID -->

                            <div>

                                <label class="block text-xs
                                           font-semibold
                                           text-slate-600
                                           uppercase
                                           tracking-wider
                                           mb-2">

                                    Student ID

                                </label>


                                <input type="text" name="student_id" id="student_id" required maxlength="30"
                                    autocomplete="off" placeholder="Student ID"
                                    value="<?= e($_POST['student_id'] ?? '') ?>" class="w-full
                                           bg-slate-100
                                           border
                                           border-slate-300
                                           rounded-lg
                                           px-4 py-3
                                           outline-none
                                           focus:ring-2
                                           focus:ring-blue-600">


                                <!-- GENERIC ERROR ONLY -->

                                <p id="studentIdError" class="hidden
                                           text-red-600
                                           text-xs
                                           mt-1">

                                    Student ID is incorrect.

                                </p>

                            </div>


                            <!-- DEPARTMENT -->

                            <div>

                                <label class="block text-xs
                                           font-semibold
                                           text-slate-600
                                           uppercase
                                           tracking-wider
                                           mb-2">

                                    Department

                                </label>


                                <select name="department" id="department" required class="w-full
                                           bg-slate-100
                                           border
                                           border-slate-300
                                           rounded-lg
                                           px-4 py-3
                                           outline-none
                                           focus:ring-2
                                           focus:ring-blue-600">

                                    <option value="">

                                        Select Department

                                    </option>


                                    <?php foreach (
                                        $departments
                                        as $dept
                                    ): ?>

                                    <option value="<?= e($dept) ?>" <?= (
                                            ($_POST['department'] ?? '')
                                            === $dept
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>>

                                        <?= e($dept) ?>

                                    </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- ACADEMIC YEAR -->

                            <div>

                                <label class="block text-xs
                                           font-semibold
                                           text-slate-600
                                           uppercase
                                           tracking-wider
                                           mb-2">

                                    Academic Year

                                </label>


                                <select name="academic_year" id="academic_year" required class="w-full
                                           bg-slate-100
                                           border
                                           border-slate-300
                                           rounded-lg
                                           px-4 py-3
                                           outline-none
                                           focus:ring-2
                                           focus:ring-blue-600">

                                    <option value="">

                                        Select Academic Year

                                    </option>


                                    <?php foreach (
                                        $years
                                        as $year
                                    ): ?>

                                    <option value="<?= e($year) ?>" <?= (
                                            ($_POST['academic_year'] ?? '')
                                            === $year
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>>

                                        <?= e($year) ?>

                                    </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- ROLL NUMBER -->

                            <div>

                                <label class="block text-xs
                                           font-semibold
                                           text-slate-600
                                           uppercase
                                           tracking-wider
                                           mb-2">

                                    Roll Number

                                </label>


                                <div class="flex">


                                    <!-- PREFIX -->

                                    <input type="text" id="roll_prefix" readonly placeholder="I.BE.CEIT-" class="w-40
                                               bg-slate-200
                                               border
                                               border-slate-300
                                               rounded-l-lg
                                               px-3 py-3
                                               text-center
                                               font-semibold
                                               text-blue-700
                                               outline-none">


                                    <!-- NUMBER -->

                                    <input type="text" name="roll_number" id="roll_number" required maxlength="2"
                                        inputmode="numeric" autocomplete="off" placeholder="2" class="w-16
                                               bg-slate-100
                                               border
                                               border-slate-300
                                               rounded-r-lg
                                               px-2 py-3
                                               text-center
                                               font-semibold
                                               outline-none
                                               focus:ring-2
                                               focus:ring-blue-600">

                                </div>


                                <p class="text-slate-500
                                           text-xs
                                           mt-1">

                                    Example:

                                    <strong id="rollExample">

                                        Select year and department

                                    </strong>

                                </p>


                                <p id="rollNumberError" class="hidden
                                           text-red-600
                                           text-xs
                                           mt-1">

                                    Enter a valid roll number.

                                </p>

                            </div>


                            <!-- GENDER -->

                            <div>

                                <label class="block text-xs
                                           font-semibold
                                           text-slate-600
                                           uppercase
                                           tracking-wider
                                           mb-2">

                                    Gender

                                </label>


                                <div class="flex gap-5 py-3">

                                    <label class="flex items-center gap-2">

                                        <input type="radio" name="gender" value="Male" required <?= (
                                                ($_POST['gender'] ?? '')
                                                === 'Male'
                                            )
                                                ? 'checked'
                                                : ''
                                            ?>>

                                        <span>
                                            Male
                                        </span>

                                    </label>


                                    <label class="flex items-center gap-2">

                                        <input type="radio" name="gender" value="Female" <?= (
                                                ($_POST['gender'] ?? '')
                                                === 'Female'
                                            )
                                                ? 'checked'
                                                : ''
                                            ?>>

                                        <span>
                                            Female
                                        </span>

                                    </label>


                                    <label class="flex items-center gap-2">

                                        <input type="radio" name="gender" value="Other" <?= (
                                                ($_POST['gender'] ?? '')
                                                === 'Other'
                                            )
                                                ? 'checked'
                                                : ''
                                            ?>>

                                        <span>
                                            Other
                                        </span>

                                    </label>

                                </div>

                            </div>


                            <!-- PHONE -->

                            <div>

                                <label class="block text-xs
                                           font-semibold
                                           text-slate-600
                                           uppercase
                                           tracking-wider
                                           mb-2">

                                    Phone

                                </label>


                                <input type="tel" name="phone" id="phone" required maxlength="11" autocomplete="off"
                                    inputmode="numeric" placeholder="09xxxxxxxxx"
                                    value="<?= e($_POST['phone'] ?? '') ?>" class="w-full
                                           bg-slate-100
                                           border
                                           border-slate-300
                                           rounded-lg
                                           px-4 py-3
                                           outline-none
                                           focus:ring-2
                                           focus:ring-blue-600">


                                <p id="phoneError" class="hidden
                                           text-red-600
                                           text-xs
                                           mt-1">

                                    Invalid phone number.

                                </p>

                            </div>


                            <!-- EMAIL -->

                            <div>

                                <label class="block text-xs
                                           font-semibold
                                           text-slate-600
                                           uppercase
                                           tracking-wider
                                           mb-2">

                                    Gmail Address

                                </label>


                                <input type="email" name="email" id="email" required maxlength="100" autocomplete="off"
                                    placeholder="example@gmail.com" value="<?= e($_POST['email'] ?? '') ?>" class="w-full
                                           bg-slate-100
                                           border
                                           border-slate-300
                                           rounded-lg
                                           px-4 py-3
                                           outline-none
                                           focus:ring-2
                                           focus:ring-blue-600">


                                <p id="emailError" class="hidden
                                           text-red-600
                                           text-xs
                                           mt-1">

                                    Please enter a valid Gmail address.

                                </p>

                            </div>


                            <!-- PASSWORD -->

                            <div>

                                <label class="block text-xs
                                           font-semibold
                                           text-slate-600
                                           uppercase
                                           tracking-wider
                                           mb-2">

                                    Password

                                </label>


                                <div class="relative">

                                    <input type="password" id="password" name="password" required minlength="8"
                                        autocomplete="new-password" placeholder="Minimum 8 characters" class="w-full
                                               bg-slate-100
                                               border
                                               border-slate-300
                                               rounded-lg
                                               px-4 py-3
                                               pr-12
                                               outline-none
                                               focus:ring-2
                                               focus:ring-blue-600">


                                    <button type="button" onclick="togglePassword(
                                            'password',
                                            'passwordIcon'
                                        )" class="absolute
                                               right-3
                                               top-1/2
                                               -translate-y-1/2
                                               text-slate-500
                                               hover:text-blue-700">

                                        <span id="passwordIcon" class="material-symbols-outlined">

                                            visibility

                                        </span>

                                    </button>

                                </div>

                            </div>


                            <!-- CONFIRM PASSWORD -->

                            <div>

                                <label class="block text-xs
                                           font-semibold
                                           text-slate-600
                                           uppercase
                                           tracking-wider
                                           mb-2">

                                    Confirm Password

                                </label>


                                <div class="relative">

                                    <input type="password" id="confirm_password" name="confirm_password" required
                                        minlength="8" autocomplete="new-password" placeholder="Confirm password" class="w-full
                                               bg-slate-100
                                               border
                                               border-slate-300
                                               rounded-lg
                                               px-4 py-3
                                               pr-12
                                               outline-none
                                               focus:ring-2
                                               focus:ring-blue-600">


                                    <button type="button" onclick="togglePassword(
                                            'confirm_password',
                                            'confirmPasswordIcon'
                                        )" class="absolute
                                               right-3
                                               top-1/2
                                               -translate-y-1/2
                                               text-slate-500
                                               hover:text-blue-700">

                                        <span id="confirmPasswordIcon" class="material-symbols-outlined">

                                            visibility

                                        </span>

                                    </button>

                                </div>

                            </div>

                        </div>


                        <!-- TERMS -->

                        <div class="flex items-start
                                   gap-3 py-2">

                            <input class="mt-1 w-4 h-4" id="terms" name="terms" type="checkbox" required <?= isset($_POST['terms'])
                                    ? 'checked'
                                    : ''
                                ?>>


                            <label class="text-sm
                                       text-slate-600" for="terms">

                                I agree to follow the club's
                                code of conduct and safety
                                guidelines. I understand that
                                membership is subject to
                                verification of student status.

                            </label>

                        </div>


                        <!-- SUBMIT -->

                        <div class="pt-4">

                            <button class="w-full
                                       bg-blue-700
                                       text-white
                                       font-semibold
                                       py-4
                                       rounded-xl
                                       hover:bg-blue-800
                                       transition
                                       flex items-center
                                       justify-center
                                       gap-2" type="submit">

                                Register Member

                                <span class="material-symbols-outlined">

                                    arrow_forward

                                </span>

                            </button>


                            <p class="text-center
                                       text-sm
                                       text-slate-600
                                       mt-4">

                                Already a member?

                                <a class="text-blue-700
                                           font-semibold
                                           hover:underline" href="login.php">

                                    Login to Portal

                                </a>

                            </p>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </main>


    <script>
    /*
|--------------------------------------------------------------------------
| Academic Year → Prefix
|--------------------------------------------------------------------------
*/

    const yearPrefixes = {

        "1st Year": "I.BE.",
        "2nd Year": "II.BE.",
        "3rd Year": "III.BE.",
        "4th Year": "IV.BE.",
        "5th Year": "V.BE.",
        "6th Year": "VI.BE."

    };


    /*
    |--------------------------------------------------------------------------
    | Department → Code
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | These codes must match the PHP codes above.
    |--------------------------------------------------------------------------
    */

    const departmentCodes = {

        "Civil Engineering": "CE",

        "Computer Engineering and Information Technology": "CEIT",

        "Electrical Power Engineering": "EP",

        "Electrical Engineering": "EC",

        "Chemical Engineering": "ChE",

        "Architectural Engineering": "Arch",

        "Mechanical Engineering": "ME",

        "Mining Engineering": "M",

        "Agricultural Engineering": "Argi",

        "Mechatronics Engineering": "MC",

        "Nuclear Engineering": "NC",

        "Biotechnology Engineering": "BioT"

    };


    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const academicYear =
        document.getElementById("academic_year");

    const department =
        document.getElementById("department");

    const rollPrefix =
        document.getElementById("roll_prefix");

    const rollNumber =
        document.getElementById("roll_number");

    const rollExample =
        document.getElementById("rollExample");


    /*
    |--------------------------------------------------------------------------
    | Update Roll Prefix
    |--------------------------------------------------------------------------
    */

    function updateRollPrefix() {

        const selectedYear =
            academicYear.value;

        const selectedDepartment =
            department.value;


        const yearPrefix =
            yearPrefixes[selectedYear] || "";

        const departmentCode =
            departmentCodes[selectedDepartment] || "";


        let prefix = "";


        if (
            yearPrefix !== "" &&
            departmentCode !== ""
        ) {

            prefix =
                yearPrefix +
                departmentCode +
                "-";

        }


        rollPrefix.value =
            prefix;


        /*
        |--------------------------------------------------------------------------
        | Example
        |--------------------------------------------------------------------------
        */

        if (prefix !== "") {

            rollExample.textContent =
                prefix + "2";

        } else {

            rollExample.textContent =
                "Select year and department";

        }


        /*
        |--------------------------------------------------------------------------
        | Keep Number Only
        |--------------------------------------------------------------------------
        */

        rollNumber.value =
            rollNumber.value.replace(
                /[^0-9]/g,
                ""
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Year Change
    |--------------------------------------------------------------------------
    */

    academicYear.addEventListener(
        "change",
        updateRollPrefix
    );


    /*
    |--------------------------------------------------------------------------
    | Department Change
    |--------------------------------------------------------------------------
    */

    department.addEventListener(
        "change",
        updateRollPrefix
    );


    /*
    |--------------------------------------------------------------------------
    | Initial
    |--------------------------------------------------------------------------
    */

    updateRollPrefix();


    /*
    |--------------------------------------------------------------------------
    | Roll Number Input
    |--------------------------------------------------------------------------
    */

    rollNumber.addEventListener(
        "input",
        function() {

            this.value =
                this.value.replace(
                    /[^0-9]/g,
                    ""
                ).slice(0, 2);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Student ID Validation
    |--------------------------------------------------------------------------
    */

    const studentId =
        document.getElementById(
            "student_id"
        );

    const studentIdError =
        document.getElementById(
            "studentIdError"
        );


    function validateStudentId() {

        const value =
            studentId.value.trim();


        const valid =
            /^မနတ-[\p{L}\p{N}-]+$/u.test(
                value
            );


        if (
            value !== "" &&
            !valid
        ) {

            studentIdError
                .classList
                .remove("hidden");


            studentId
                .classList
                .add("border-red-500");


            return false;

        }


        studentIdError
            .classList
            .add("hidden");


        studentId
            .classList
            .remove("border-red-500");


        return true;

    }


    studentId.addEventListener(
        "input",
        validateStudentId
    );


    /*
    |--------------------------------------------------------------------------
    | Phone Validation
    |--------------------------------------------------------------------------
    */

    const phone =
        document.getElementById(
            "phone"
        );

    const phoneError =
        document.getElementById(
            "phoneError"
        );


    function validatePhone() {

        phone.value =
            phone.value
            .replace(/\D/g, "")
            .slice(0, 11);


        const valid =
            /^09[0-9]{9}$/.test(
                phone.value
            );


        if (
            phone.value !== "" &&
            !valid
        ) {

            phoneError
                .classList
                .remove("hidden");


            phone
                .classList
                .add("border-red-500");


            return false;

        }


        phoneError
            .classList
            .add("hidden");


        phone
            .classList
            .remove("border-red-500");


        return true;

    }


    phone.addEventListener(
        "input",
        validatePhone
    );


    /*
    |--------------------------------------------------------------------------
    | Gmail Validation
    |--------------------------------------------------------------------------
    */

    const email =
        document.getElementById(
            "email"
        );

    const emailError =
        document.getElementById(
            "emailError"
        );


    function validateEmail() {

        const value =
            email.value
            .trim()
            .toLowerCase();


        const valid =
            /^[a-zA-Z0-9._%+-]+@gmail\.com$/
            .test(value);


        if (
            value !== "" &&
            !valid
        ) {

            emailError
                .classList
                .remove("hidden");


            email
                .classList
                .add("border-red-500");


            return false;

        }


        emailError
            .classList
            .add("hidden");


        email
            .classList
            .remove("border-red-500");


        return true;

    }


    email.addEventListener(
        "input",
        validateEmail
    );


    /*
    |--------------------------------------------------------------------------
    | Form Submit
    |--------------------------------------------------------------------------
    */

    document
        .getElementById(
            "registrationForm"
        )
        .addEventListener(
            "submit",
            function(event) {

                let valid = true;


                /*
                |--------------------------------------------------------------------------
                | Student ID
                |--------------------------------------------------------------------------
                */

                if (
                    !validateStudentId()
                ) {

                    valid = false;

                }


                /*
                |--------------------------------------------------------------------------
                | Academic Year
                |--------------------------------------------------------------------------
                */

                if (
                    !yearPrefixes[
                        academicYear.value
                    ]
                ) {

                    alert(
                        "Please select a valid academic year."
                    );

                    valid = false;

                }


                /*
                |--------------------------------------------------------------------------
                | Department
                |--------------------------------------------------------------------------
                */

                if (
                    !departmentCodes[
                        department.value
                    ]
                ) {

                    alert(
                        "Please select a valid department."
                    );

                    valid = false;

                }


                /*
                |--------------------------------------------------------------------------
                | Roll Number
                |--------------------------------------------------------------------------
                */

                const rollError =
                    document.getElementById(
                        "rollNumberError"
                    );


                const rollValue =
                    rollNumber.value.trim();


                if (
                    !/^[0-9]{1,2}$/.test(
                        rollValue
                    )
                ) {

                    rollError
                        .classList
                        .remove("hidden");


                    rollNumber
                        .classList
                        .add(
                            "border-red-500"
                        );


                    valid = false;

                } else {

                    rollError
                        .classList
                        .add("hidden");


                    rollNumber
                        .classList
                        .remove(
                            "border-red-500"
                        );

                }


                /*
                |--------------------------------------------------------------------------
                | Phone
                |--------------------------------------------------------------------------
                */

                if (
                    !validatePhone()
                ) {

                    valid = false;

                }


                /*
                |--------------------------------------------------------------------------
                | Gmail
                |--------------------------------------------------------------------------
                */

                if (
                    !validateEmail()
                ) {

                    valid = false;

                }


                /*
                |--------------------------------------------------------------------------
                | Password
                |--------------------------------------------------------------------------
                */

                const password =
                    document.getElementById(
                        "password"
                    );


                const confirmPassword =
                    document.getElementById(
                        "confirm_password"
                    );


                if (
                    password.value.length < 8
                ) {

                    alert(
                        "Password must be at least 8 characters."
                    );

                    valid = false;

                }


                /*
                |--------------------------------------------------------------------------
                | Confirm Password
                |--------------------------------------------------------------------------
                */

                if (
                    password.value !==
                    confirmPassword.value
                ) {

                    alert(
                        "Passwords do not match."
                    );

                    valid = false;

                }


                /*
                |--------------------------------------------------------------------------
                | Build Complete Roll Number
                |--------------------------------------------------------------------------
                */

                if (valid) {

                    const yearPrefix =
                        yearPrefixes[
                            academicYear.value
                        ];


                    const departmentCode =
                        departmentCodes[
                            department.value
                        ];


                    const number =
                        rollNumber.value.trim();


                    rollNumber.value =
                        yearPrefix +
                        departmentCode +
                        "-" +
                        number;

                }


                /*
                |--------------------------------------------------------------------------
                | Stop Submission
                |--------------------------------------------------------------------------
                */

                if (!valid) {

                    event.preventDefault();

                }

            }
        );


    /*
    |--------------------------------------------------------------------------
    | Profile Image Preview
    |--------------------------------------------------------------------------
    */

    function previewImage(input) {

        const preview =
            document.getElementById(
                "photoPreview"
            );

        const icon =
            document.getElementById(
                "photoIcon"
            );

        const container =
            document.getElementById(
                "photoPreviewContainer"
            );


        if (
            input.files &&
            input.files[0]
        ) {

            const file =
                input.files[0];


            /*
            |--------------------------------------------------------------------------
            | Size
            |--------------------------------------------------------------------------
            */

            if (
                file.size >
                2 * 1024 * 1024
            ) {

                alert(
                    "Profile picture must be smaller than 2MB."
                );


                input.value = "";

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Type
            |--------------------------------------------------------------------------
            */

            const allowedTypes = [

                "image/jpeg",
                "image/png"

            ];


            if (
                !allowedTypes.includes(
                    file.type
                )
            ) {

                alert(
                    "Only JPG and PNG images are allowed."
                );


                input.value = "";

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Preview
            |--------------------------------------------------------------------------
            */

            const reader =
                new FileReader();


            reader.onload =
                function(event) {

                    preview.src =
                        event.target.result;


                    preview.classList
                        .remove(
                            "hidden"
                        );


                    icon.classList
                        .add(
                            "hidden"
                        );


                    container.classList
                        .remove(
                            "border-dashed"
                        );


                    container.classList
                        .add(
                            "border-solid",
                            "border-blue-600"
                        );

                };


            reader.readAsDataURL(
                file
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Password
    |--------------------------------------------------------------------------
    */

    function togglePassword(
        inputId,
        iconId
    ) {

        const input =
            document.getElementById(
                inputId
            );


        const icon =
            document.getElementById(
                iconId
            );


        if (
            input.type ===
            "password"
        ) {

            input.type =
                "text";


            icon.textContent =
                "visibility_off";

        } else {

            input.type =
                "password";


            icon.textContent =
                "visibility";

        }

    }
    </script>

</body>

</html>