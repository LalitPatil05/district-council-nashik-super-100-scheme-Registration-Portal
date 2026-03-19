<?php
require_once __DIR__ . '/partials/header.php';
?>

<section class="hero">
  <div>
    <span class="hero-badge">Super 50 • 2026-28</span>
    <h1>सुपर 50 मोफत प्रशिक्षण योजना २०२६-२८</h1>
    <p>नाशिक जिल्ह्यातील ग्रामीण भागातील अनुसूचित जाती (SC), अनुसूचित जमाती (ST) आणि इतर संवर्गातील प्रवर्गाच्या (Other) वि‌द्यार्थ्यांसाठी NEET व JEE / Advance या परीक्षेसाठी दोन वर्षाचे निवासी निःशुल्क कोचिंग
</p>
    <div class="hero-actions">
      <?php if (!is_logged_in()): ?>
        <a href="register.php"><button>Apply Now</button></a>
        <a href="login.php"><button class="secondary">Login</button></a>
      <?php else: ?>
        <?php if (($_SESSION['role'] ?? '') === 'student'): ?>
          <a href="student_form.php"><button>Start Application</button></a>
          <a href="student_status.php"><button class="secondary">View Status</button></a>
        <?php else: ?>
          <a href="officer_list.php"><button>Review Applications</button></a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
  <div class="card">
    <h3>योजनेची ठळक वैशिष्ट्ये</h3>
    <p class="muted">नाशिकमधील ग्रामीण विद्यार्थ्यांच्या NEET आणि JEE तयारीसाठी जिल्हास्तरीय उपक्रम.</p>
    <div class="pill">१०० जागा • अनुसूचित जाती/जमाती/इतर</div>
    <p class="muted">निवडीमध्ये पहिली फेरी (तालुका) आणि दुसरी फेरी (जिल्हा) चाचण्यांचा समावेश आहे.</p>
  </div>
</section>

<section class="info-grid">
  <div class="card">
    <h3>पात्रता</h3>
    <ul>
      <li>ग्रामीण नाशिक रहिवासी</li>
      <li>मार्च २०२६ मध्ये १० व्या स्थानासाठी हजर.</li>
      <li>कुटुंबाचे उत्पन्न २ लाखांपेक्षा कमी.</li>
      <li>२०२६-२७ मध्ये विज्ञान शाखेतील प्रवेश.</li>
    </ul>
  </div>
  <div class="card">
    <h3>आवश्यक कागदपत्रे</h3>
    <ul>
      <li>फोटो</li>
      <li>आधार कार्ड</li>
      <li>उत्पन्न प्रमाणपत्र</li>
      <li>अधिवास प्रमाणपत्र</li>
    </ul>
  </div>
  <div class="card">
    <h3>प्रक्रिया</h3>
    <ol>
      <li>नोंदणी करा आणि अर्ज भरा.</li>
      <li>कागदपत्रे अपलोड करा आणि सबमिट करा.</li>
      <li>अधिकारी पडताळणी करून स्थिती अद्ययावत करतो.</li>
    </ol>
  </div>
</section>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
