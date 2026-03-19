<?php
return [
  'upload_dir' => __DIR__ . '/../storage/uploads',
  'pdf_dir' => __DIR__ . '/../storage/pdfs',
  'max_upload_size' => 5 * 1024 * 1024,
  'allowed_mime' => ['application/pdf', 'image/jpeg', 'image/png'],
  'required_docs' => ['photo', 'aadhaar', 'income', 'domicile'],
  'doc_labels' => [
    'photo' => 'Photo',
    'aadhaar' => 'Aadhaar Card',
    'income' => 'Income Certificate',
    'domicile' => 'Domicile Certificate',
    'other' => 'Other Document',
  ],
];
