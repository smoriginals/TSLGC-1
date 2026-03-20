$rp = "C:\Users\acera\Desktop\New-Roi\frontend"
$pages = "index.html","about.html","businesses.html","income.html","pricing.html","faq.html","contact.html","join.html"

foreach ($fn in $pages) {
  $p = "$rp\$fn"
  $c = [IO.File]::ReadAllText($p)

  # Footer headings
  $c = $c.Replace('<h5 class="footer-heading">Quick Links</h5>', '<h5 class="footer-heading" data-en="Quick Links" data-hi="त्वरित लिंक">Quick Links</h5>')
  $c = $c.Replace('<h5 class="footer-heading">Platform</h5>', '<h5 class="footer-heading" data-en="Platform" data-hi="प्लेटफॉर्म">Platform</h5>')
  $c = $c.Replace('<h5 class="footer-heading">Connect With Us</h5>', '<h5 class="footer-heading" data-en="Connect With Us" data-hi="हमसे संपर्क करें">Connect With Us</h5>')

  # Footer links
  $c = $c.Replace('<a href="index.html">Home</a>', '<a href="index.html" data-en="Home" data-hi="होम">Home</a>')
  $c = $c.Replace('<a href="about.html">About</a>', '<a href="about.html" data-en="About" data-hi="हमारे बारे में">About</a>')
  $c = $c.Replace('<a href="businesses.html">Businesses</a>', '<a href="businesses.html" data-en="Businesses" data-hi="व्यवसाय">Businesses</a>')
  $c = $c.Replace('<a href="pricing.html">Pricing</a>', '<a href="pricing.html" data-en="Pricing" data-hi="मूल्य">Pricing</a>')
  $c = $c.Replace('<a href="faq.html">FAQ</a>', '<a href="faq.html" data-en="FAQ" data-hi="सामान्य प्रश्न">FAQ</a>')
  $c = $c.Replace('<a href="income.html">Alliance Market</a>', '<a href="income.html" data-en="Alliance Market" data-hi="एलायंस मार्केट">Alliance Market</a>')
  $c = $c.Replace('<a href="income.html">Digital Training</a>', '<a href="income.html" data-en="Digital Training" data-hi="डिजिटल ट्रेनिंग">Digital Training</a>')
  $c = $c.Replace('<a href="join.html">Join Now</a>', '<a href="join.html" data-en="Join Now" data-hi="अभी जुड़ें">Join Now</a>')
  $c = $c.Replace('<a href="income.html">Rewards</a>', '<a href="income.html" data-en="Rewards" data-hi="पुरस्कार">Rewards</a>')
  $c = $c.Replace('<a href="about.html">Vision 2030</a>', '<a href="about.html" data-en="Vision 2030" data-hi="विजन 2030">Vision 2030</a>')

  # Footer legal note
  $c = $c.Replace(
    '<p class="footer-legal-note">TSLGC is a legal direct selling company. Income depends on individual effort and team performance.</p>',
    '<p class="footer-legal-note" data-en="TSLGC is a legal direct selling company. Income depends on individual effort and team performance." data-hi="TSLGC एक कानूनी डायरेक्ट सेलिंग कंपनी है। आय व्यक्तिगत प्रयास और टीम प्रदर्शन पर निर्भर करती है।">TSLGC is a legal direct selling company. Income depends on individual effort and team performance.</p>'
  )

  # Footer motto text wrapped in span
  $c = $c.Replace(
    '<i class="fa-solid fa-infinity me-2"></i>कभी न रुकने वाली इनकम',
    '<i class="fa-solid fa-infinity me-2"></i><span data-en="Unstoppable Income" data-hi="कभी न रुकने वाली इनकम">कभी न रुकने वाली इनकम</span>'
  )

  # Footer description
  $c = $c.Replace(
    'भारत का सबसे बड़ा Alliance Market — 1 ID, 101 Businesses, अनंत संभावनाएं।',
    '<span data-en="India''s Biggest Alliance Market — 1 ID, 101 Businesses, Infinite Possibilities." data-hi="भारत का सबसे बड़ा Alliance Market — 1 ID, 101 Businesses, अनंत संभावनाएं।">भारत का सबसे बड़ा Alliance Market — 1 ID, 101 Businesses, अनंत संभावनाएं।</span>'
  )

  [IO.File]::WriteAllText($p, $c)
  Write-Host "done $fn"
}
