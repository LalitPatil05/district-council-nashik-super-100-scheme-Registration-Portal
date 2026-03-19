<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/_helpers.php';

require_role('student');
$config = require __DIR__ . '/../config/app.php';

$errors = [];
$app = null;
$docs = [];
$docsByType = [];

$talukaOptions = [
  'Nashik','Igatpuri','Trimbakeshwar','Dindori','Peth','Kalwan','Surgana','Chandwad',
  'Deola','Baglan (Satana)','Malegaon','Nandgaon','Yeola','Niphad','Sinnar'
];

$courseOptions = ['NEET', 'JEE', 'Both'];
$genderOptions = ['Male', 'Female', 'Transgender', 'Prefer not to say'];
$yesNo = ['Yes', 'No'];
$categoryOptions = ['SC', 'ST', 'Other'];

$stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$formValues = [
  'full_name' => $user['name'] ?? '',
  'dob' => '',
  'gender' => '',
  'mobile' => '',
  'alt_mobile' => '',
  'email' => $user['email'] ?? '',
  'address' => '',
  'village' => '',
  'taluka' => '',
  'pin_code' => '',
  'school_name' => '',
  'school_address' => '',
  'appearing_10th' => '',
  'category' => '',
  'disability' => '',
  'udid_number' => '',
  'family_income_lt2l' => '',
  'aadhaar' => '',
  'course_interest' => '',
  'exam_city' => '',
  'declaration_confirm' => '0',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();

  $fields = [
    'full_name' => trim($_POST['full_name'] ?? ''),
    'dob' => trim($_POST['dob'] ?? ''),
    'gender' => trim($_POST['gender'] ?? ''),
    'mobile' => trim($_POST['mobile'] ?? ''),
    'alt_mobile' => trim($_POST['alt_mobile'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'address' => trim($_POST['address'] ?? ''),
    'village' => trim($_POST['village'] ?? ''),
    'taluka' => trim($_POST['taluka'] ?? ''),
    'pin_code' => trim($_POST['pin_code'] ?? ''),
    'school_name' => trim($_POST['school_name'] ?? ''),
    'school_address' => trim($_POST['school_address'] ?? ''),
    'appearing_10th' => trim($_POST['appearing_10th'] ?? ''),
    'category' => trim($_POST['category'] ?? ''),
    'disability' => trim($_POST['disability'] ?? ''),
    'udid_number' => trim($_POST['udid_number'] ?? ''),
    'family_income_lt2l' => trim($_POST['family_income_lt2l'] ?? ''),
    'aadhaar' => trim($_POST['aadhaar'] ?? ''),
    'course_interest' => trim($_POST['course_interest'] ?? ''),
    'exam_city' => trim($_POST['exam_city'] ?? ''),
    'declaration_confirm' => isset($_POST['declaration_confirm']) ? '1' : '0',
  ];

  $formValues = array_merge($formValues, $fields);

  if ($fields['full_name'] === '') { $errors[] = 'Full name is required.'; }
  if ($fields['dob'] === '') { $errors[] = 'Date of birth is required.'; }
  if (!in_array($fields['gender'], $genderOptions, true)) { $errors[] = 'Gender is required.'; }
  if ($fields['mobile'] === '') { $errors[] = 'Mobile number is required.'; }
  if ($fields['alt_mobile'] === '') { $errors[] = 'Alternate mobile number is required.'; }
  if ($fields['email'] === '' || !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required.'; }
  if ($fields['address'] === '') { $errors[] = 'Address is required.'; }
  if ($fields['village'] === '') { $errors[] = 'Village name is required.'; }
  if (!in_array($fields['taluka'], $talukaOptions, true)) { $errors[] = 'Taluka is required.'; }
  if ($fields['pin_code'] === '') { $errors[] = 'Pin code is required.'; }
  if ($fields['school_name'] === '') { $errors[] = 'School name is required.'; }
  if ($fields['school_address'] === '') { $errors[] = 'School address is required.'; }
  if (!in_array($fields['appearing_10th'], $yesNo, true)) { $errors[] = 'Please select appearing for 10th.'; }
  if (!in_array($fields['category'], $categoryOptions, true)) { $errors[] = 'Category is required.'; }
  if (!in_array($fields['disability'], $yesNo, true)) { $errors[] = 'Please select disability status.'; }
  if ($fields['disability'] === 'Yes' && $fields['udid_number'] === '') { $errors[] = 'UDID number is required.'; }
  if (!in_array($fields['family_income_lt2l'], $yesNo, true)) { $errors[] = 'Please confirm family income status.'; }
  if ($fields['aadhaar'] === '') { $errors[] = 'Aadhaar number is required.'; }
  if (!in_array($fields['course_interest'], $courseOptions, true)) { $errors[] = 'Course interest is required.'; }
  if (!in_array($fields['exam_city'], $talukaOptions, true)) { $errors[] = 'Exam city is required.'; }
  if ($fields['declaration_confirm'] !== '1') { $errors[] = 'You must confirm the declaration.'; }

  $appId = trim($_POST['app_id'] ?? '');

  if (!$errors) {
    if ($appId !== '') {
      $stmt = $pdo->prepare('SELECT * FROM applications WHERE application_id = ? AND user_id = ?');
      $stmt->execute([$appId, $_SESSION['user_id']]);
      $app = $stmt->fetch();

      if (!$app) {
        $errors[] = 'Application not found.';
      } elseif (!in_array($app['status'], ['draft', 'sent_back'], true)) {
        $errors[] = 'Application cannot be edited in current status.';
      } else {
        $stmt = $pdo->prepare(
          'UPDATE applications SET full_name = ?, dob = ?, gender = ?, mobile = ?, alt_mobile = ?, email = ?, address = ?, village = ?, taluka = ?, pin_code = ?, school_name = ?, school_address = ?, appearing_10th = ?, category = ?, disability = ?, udid_number = ?, family_income_lt2l = ?, aadhaar = ?, course_interest = ?, exam_city = ?, declaration_confirm = ? WHERE id = ?'
        );
        $stmt->execute([
          $fields['full_name'], $fields['dob'], $fields['gender'], $fields['mobile'], $fields['alt_mobile'], $fields['email'],
          $fields['address'], $fields['village'], $fields['taluka'], $fields['pin_code'], $fields['school_name'], $fields['school_address'],
          $fields['appearing_10th'], $fields['category'], $fields['disability'], $fields['udid_number'], $fields['family_income_lt2l'],
          $fields['aadhaar'], $fields['course_interest'], $fields['exam_city'], $fields['declaration_confirm'], $app['id']
        ]);
        set_flash('Draft updated.');
        header('Location: student_form.php?app_id=' . urlencode($appId));
        exit;
      }
    } else {
      $newAppId = generate_app_id();
      $stmt = $pdo->prepare(
        'INSERT INTO applications (application_id, user_id, full_name, dob, gender, mobile, alt_mobile, email, address, village, taluka, pin_code, school_name, school_address, appearing_10th, category, disability, udid_number, family_income_lt2l, aadhaar, course_interest, exam_city, declaration_confirm) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
      );
      $stmt->execute([
        $newAppId, $_SESSION['user_id'], $fields['full_name'], $fields['dob'], $fields['gender'], $fields['mobile'], $fields['alt_mobile'], $fields['email'],
        $fields['address'], $fields['village'], $fields['taluka'], $fields['pin_code'], $fields['school_name'], $fields['school_address'],
        $fields['appearing_10th'], $fields['category'], $fields['disability'], $fields['udid_number'], $fields['family_income_lt2l'],
        $fields['aadhaar'], $fields['course_interest'], $fields['exam_city'], $fields['declaration_confirm']
      ]);
      set_flash('Draft created.');
      header('Location: student_form.php?app_id=' . urlencode($newAppId));
      exit;
    }
  }
}

$appId = trim($_GET['app_id'] ?? '');
if ($appId !== '') {
  $stmt = $pdo->prepare('SELECT * FROM applications WHERE application_id = ? AND user_id = ?');
  $stmt->execute([$appId, $_SESSION['user_id']]);
  $app = $stmt->fetch();

  if ($app) {
    foreach ($formValues as $key => $value) {
      if (array_key_exists($key, $app)) {
        $formValues[$key] = (string)$app[$key];
      }
    }
    $stmt = $pdo->prepare('SELECT * FROM documents WHERE application_id = ? ORDER BY uploaded_at DESC');
    $stmt->execute([$app['id']]);
    $docs = $stmt->fetchAll();
    foreach ($docs as $doc) {
      $docsByType[$doc['doc_type']] = $doc;
    }
  }
}

require_once __DIR__ . '/partials/header.php';
?>
<div class="form-page">
  <div class="form-meta">
    <div class="pill">Application Form</div>
    <?php if ($app): ?>
      <div class="pill">ID: <?php echo h($app['application_id']); ?> | Status: <?php echo h($app['status']); ?></div>
    <?php endif; ?>
  </div>
  <h2>Application Form</h2>
  <p class="muted">हा फॉर्म फक्त इंग्रजी भाषेतच व अंकातच भरावा. मराठी किंवा इतर भाषांमध्ये भरलेला फॉर्म ग्राह्य धरला जाणार नाही.</p>
  <?php if ($errors): ?>
    <div class="notice">
      <?php foreach ($errors as $err): ?>
        <div><?php echo h($err); ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($app && !in_array($app['status'], ['draft', 'sent_back'], true)): ?>
    <div class="notice">This application is <?php echo h($app['status']); ?> and cannot be edited.</div>
  <?php endif; ?>

  <form method="post" class="form-shell">
    <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
    <input type="hidden" name="app_id" value="<?php echo h($appId); ?>">

    <section class="form-section">
      <div class="section-head">
        <div class="section-title">विद्यार्थ्यांची माहिती</div>
      </div>
      <div class="form-row">
        <div>
          <label>विद्यार्थ्याचे संपूर्ण नाव<span class="req">*</span></label>
          <input type="text" name="full_name" value="<?php echo h($formValues['full_name']); ?>" required>
        </div>
        <div>
          <label>जन्मतारीख<span class="req">*</span></label>
          <input type="date" name="dob" value="<?php echo h($formValues['dob']); ?>" required>
        </div>
      </div>
        <br>
      <div class="form-row">
        <div>
          <label>लिंग<span class="req">*</span></label>
          <select name="gender" required>
            <option value="">निवडा</option>
            <?php foreach ($genderOptions as $opt): ?>
              <option value="<?php echo h($opt); ?>" <?php echo ($formValues['gender'] === $opt) ? 'selected' : ''; ?>><?php echo h($opt); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>विद्यार्थ्यांची ई-मेल आय डी<span class="req"></span></label>
          <input type="email" name="email" value="<?php echo h($formValues['email']); ?>" placeholder="optional" required>
        </div>
      </div>
    </section>

    <section class="form-section">
      <div class="section-head">
        <div class="section-title">विद्यार्थी संपर्क माहिती</div>
      </div>
      <div class="form-row">
        <div>
          <label>विद्यार्थ्यांचा मोबाईल क्रमांक<span class="req">*</span></label>
          <input type="text" name="mobile" value="<?php echo h($formValues['mobile']); ?>" required>
          <div class="field-hint">विद्यार्थ्यांचा नसल्यास पालकांचा मोबाईल क्रमांक टाका.</div>
        </div>
        <div>
          <label>पर्यायी मोबाईल नंबर<span class="req">*</span></label>
          <input type="text" name="alt_mobile" value="<?php echo h($formValues['alt_mobile']); ?>" required>
        </div>
      </div>
      <br>

      <label>संपूर्ण पत्ता पिन कोड सहित<span class="req">*</span></label>
      <textarea name="address" rows="3" required><?php echo h($formValues['address']); ?></textarea><br><br>
      <div class="form-row-3">
        <div>
          <label>गावाचे नाव<span class="req">*</span></label>
          <input type="text" name="village" value="<?php echo h($formValues['village']); ?>" required>
        </div>
        <div>
          <label>तालुका<span class="req">*</span></label>
          <select name="taluka" required>
            <option value="">निवडा</option>
            <?php foreach ($talukaOptions as $opt): ?>
              <option value="<?php echo h($opt); ?>" <?php echo ($formValues['taluka'] === $opt) ? 'selected' : ''; ?>><?php echo h($opt); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>पिन कोड<span class="req">*</span></label>
          <input type="text" name="pin_code" value="<?php echo h($formValues['pin_code']); ?>" required>
        </div>
      </div>
    </section>
    <br>

    <section class="form-section">
      <div class="section-head">
        <div class="section-title">विद्यार्थांची शैक्षणिक माहिती</div>
      </div>
      <div class="form-row">
        <div>
          <label>शाळेचे नाव<span class="req">*</span></label>
          <input type="text" name="school_name" value="<?php echo h($formValues['school_name']); ?>" required>
        </div>
        <div>
          <label>शाळेचा पत्ता<span class="req">*</span></label>
          <input type="text" name="school_address" value="<?php echo h($formValues['school_address']); ?>" required>
        </div>
      </div>
      <br>

      <div class="form-row">
        <div>
          <label>मार्च 2026 मध्ये 10th चा परीक्षा देणार आहे का?<span class="req">*</span></label>
          <select name="appearing_10th" required>
            <option value="">निवडा</option>
            <?php foreach ($yesNo as $opt): ?>
              <option value="<?php echo h($opt); ?>" <?php echo ($formValues['appearing_10th'] === $opt) ? 'selected' : ''; ?>><?php echo h($opt); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>जाती प्रवर्ग<span class="req">*</span></label>
          <select name="category" required>
            <option value="">निवडा</option>
            <?php foreach ($categoryOptions as $opt): ?>
              <option value="<?php echo h($opt); ?>" <?php echo ($formValues['category'] === $opt) ? 'selected' : ''; ?>><?php echo h($opt); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </section>
    <br>

    <section class="form-section">
      <div class="section-head">
        <div class="section-title">पात्रता</div>
      </div>
      <div class="form-row">
        <div>
          <label>विद्यार्थी दिव्यांग आहे का?<span class="req">*</span></label>
          <select name="disability" required>
            <option value="">निवडा</option>
            <?php foreach ($yesNo as $opt): ?>
              <option value="<?php echo h($opt); ?>" <?php echo ($formValues['disability'] === $opt) ? 'selected' : ''; ?>><?php echo h($opt); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>UDID क्रमांक</label>
          <input type="text" name="udid_number" value="<?php echo h($formValues['udid_number']); ?>">
        </div>
      </div>
      <br>

      <div class="form-row">
        <div>
          <label>कुटुंबाचे वार्षिक उत्पन्न रुपये 2 लाखांपेक्षा कमी आहे का?<span class="req">*</span></label>
          <select name="family_income_lt2l" required>
            <option value="">निवडा</option>
            <?php foreach ($yesNo as $opt): ?>
              <option value="<?php echo h($opt); ?>" <?php echo ($formValues['family_income_lt2l'] === $opt) ? 'selected' : ''; ?>><?php echo h($opt); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>विद्यार्थ्यांचा आधार कार्ड नंबर<span class="req">*</span></label>
          <input type="text" name="aadhaar" value="<?php echo h($formValues['aadhaar']); ?>" required>
        </div>
      </div>
    </section>
    <br>

    <section class="form-section">
      <div class="section-head">
        <div class="section-title">अभ्यासक्रम निवडा</div>
      </div>
      <div class="form-row">
        <div>
          <label>कोणत्या अभ्यासक्रमासाठी इच्छुक आहात?<span class="req">*</span></label>
          <select name="course_interest" required>
            <option value="">निवडा</option>
            <?php foreach ($courseOptions as $opt): ?>
              <option value="<?php echo h($opt); ?>" <?php echo ($formValues['course_interest'] === $opt) ? 'selected' : ''; ?>><?php echo h($opt); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>परीक्षेसाठी निवडलेला तालुका<span class="req">*</span></label>
          <select name="exam_city" required>
            <option value="">निवडा</option>
            <?php foreach ($talukaOptions as $opt): ?>
              <option value="<?php echo h($opt); ?>" <?php echo ($formValues['exam_city'] === $opt) ? 'selected' : ''; ?>><?php echo h($opt); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </section>
    <br>
    <section class="form-section">
      <div class="section-head">
        <div class="section-title">सहमति</div>
      </div>
      <label>
        <input type="checkbox" name="declaration_confirm" value="1" <?php echo ($formValues['declaration_confirm'] === '1') ? 'checked' : ''; ?>>
        वरील सर्व माहिती खरी आणि बरोबर आहे. मी पात्रता निकष पूर्ण करतो/करते आणि आवश्यक कागदपत्रे अपलोड करेन याची मला जाणीव आहे. मला समजते की चुकीची माहिती दिल्यास माझा अर्ज रद्द केला जाऊ शकतो.
      </label>
    </section>
    <div class="form-actions">
      <button type="submit">सेव करा</button>
    </div>
  </form>
</div>

<?php if ($app): ?>
  <div class="card">
    <h3>कार्यालयीन कागदपत्रे अपलोड करा</h3>
    <p class="muted">Required: Photo, Aadhaar, Income Certificate, Domicile Certificate.</p>
    <form method="post" action="student_upload.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
      <input type="hidden" name="app_id" value="<?php echo h($appId); ?>">
      <div class="form-row">
        <div>
          <label>Document प्रकार</label>
          <select name="doc_type" required>
            <?php foreach ($config['doc_labels'] as $value => $label): ?>
              <option value="<?php echo h($value); ?>"><?php echo h($label); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>फाइल निवडा</label>
          <input type="file" name="doc" required>
        </div>
      </div>
      <br>
      <button type="submit">Upload</button>
    </form>

    <?php if ($docs): ?>
      <h4>Uploaded Documents</h4>
      <table class="table">
        <thead>
          <tr>
            <th>Type</th>
            <th>File</th>
            <th>Uploaded</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($docs as $doc): ?>
            <tr>
              <td><?php echo h($config['doc_labels'][$doc['doc_type']] ?? $doc['doc_type']); ?></td>
              <td><?php echo h($doc['file_name']); ?></td>
              <td><?php echo h($doc['uploaded_at']); ?></td>
              <td><a href="download_doc.php?id=<?php echo h($doc['id']); ?>">Download</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <div class="notice">
      <strong>Missing required documents:</strong>
      <?php
        $missing = [];
        foreach ($config['required_docs'] as $req) {
          if (!isset($docsByType[$req])) {
            $missing[] = $config['doc_labels'][$req] ?? $req;
          }
        }
      ?>
      <?php echo $missing ? h(implode(', ', $missing)) : 'All required documents uploaded.'; ?>
    </div>
  </div>

  <div class="card">
    <h3>Submit Application</h3>
    <?php if (in_array($app['status'], ['draft', 'sent_back'], true)): ?>
      <form method="post" action="student_submit.php">
        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
        <input type="hidden" name="app_id" value="<?php echo h($appId); ?>">
        <button type="submit">Submit Application</button>
      </form>
    <?php else: ?>
      <div class="notice">Current status: <?php echo h($app['status']); ?></div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
