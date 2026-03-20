# -*- coding: utf-8 -*-
import os

ROOT = r"C:\Users\acera\Desktop\New-Roi\frontend"

def fix(path, replacements):
    with open(path, 'r', encoding='utf-8') as f:
        c = f.read()
    for old, new in replacements:
        c = c.replace(old, new)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(c)
    print(f"done {os.path.basename(path)}")

PAGES = ["index.html","about.html","businesses.html","income.html",
         "pricing.html","faq.html","contact.html","join.html"]

# ── FOOTER (all 8 pages) ──────────────────────────────────────
FOOTER = [
    ('<h5 class="footer-heading">Quick Links</h5>',
     '<h5 class="footer-heading" data-en="Quick Links" data-hi="त्वरित लिंक">Quick Links</h5>'),
    ('<h5 class="footer-heading">Platform</h5>',
     '<h5 class="footer-heading" data-en="Platform" data-hi="प्लेटफॉर्म">Platform</h5>'),
    ('<h5 class="footer-heading">Connect With Us</h5>',
     '<h5 class="footer-heading" data-en="Connect With Us" data-hi="हमसे संपर्क करें">Connect With Us</h5>'),
    ('<a href="index.html">Home</a>',
     '<a href="index.html" data-en="Home" data-hi="होम">Home</a>'),
    ('<a href="about.html">About</a>',
     '<a href="about.html" data-en="About" data-hi="हमारे बारे में">About</a>'),
    ('<a href="businesses.html">Businesses</a>',
     '<a href="businesses.html" data-en="Businesses" data-hi="व्यवसाय">Businesses</a>'),
    ('<a href="pricing.html">Pricing</a>',
     '<a href="pricing.html" data-en="Pricing" data-hi="मूल्य">Pricing</a>'),
    ('<a href="faq.html">FAQ</a>',
     '<a href="faq.html" data-en="FAQ" data-hi="सामान्य प्रश्न">FAQ</a>'),
    ('<a href="income.html">Alliance Market</a>',
     '<a href="income.html" data-en="Alliance Market" data-hi="एलायंस मार्केट">Alliance Market</a>'),
    ('<a href="income.html">Digital Training</a>',
     '<a href="income.html" data-en="Digital Training" data-hi="डिजिटल ट्रेनिंग">Digital Training</a>'),
    ('<a href="join.html">Join Now</a>',
     '<a href="join.html" data-en="Join Now" data-hi="अभी जुड़ें">Join Now</a>'),
    ('<a href="income.html">Rewards</a>',
     '<a href="income.html" data-en="Rewards" data-hi="पुरस्कार">Rewards</a>'),
    ('<a href="about.html">Vision 2030</a>',
     '<a href="about.html" data-en="Vision 2030" data-hi="विजन 2030">Vision 2030</a>'),
    ('<p class="footer-legal-note">TSLGC is a legal direct selling company. Income depends on individual effort and team performance.</p>',
     '<p class="footer-legal-note" data-en="TSLGC is a legal direct selling company. Income depends on individual effort and team performance." data-hi="TSLGC एक कानूनी डायरेक्ट सेलिंग कंपनी है। आय व्यक्तिगत प्रयास और टीम प्रदर्शन पर निर्भर करती है।">TSLGC is a legal direct selling company. Income depends on individual effort and team performance.</p>'),
    ('<i class="fa-solid fa-infinity me-2"></i>कभी न रुकने वाली इनकम',
     '<i class="fa-solid fa-infinity me-2"></i><span data-en="Unstoppable Income" data-hi="कभी न रुकने वाली इनकम">कभी न रुकने वाली इनकम</span>'),
    ('भारत का सबसे बड़ा Alliance Market — 1 ID, 101 Businesses, अनंत संभावनाएं।',
     '<span data-en="India\'s Biggest Alliance Market — 1 ID, 101 Businesses, Infinite Possibilities." data-hi="भारत का सबसे बड़ा Alliance Market — 1 ID, 101 Businesses, अनंत संभावनाएं।">भारत का सबसे बड़ा Alliance Market — 1 ID, 101 Businesses, अनंत संभावनाएं।</span>'),
]

for fn in PAGES:
    fix(os.path.join(ROOT, fn), FOOTER)

# ── INDEX.HTML specific ──────────────────────────────────────
INDEX = [
    # Title
    ('<title>TSLGC Service Pvt Ltd – India\'s Biggest Alliance Market</title>',
     '<title>TSLGC – India\'s Biggest Alliance Market</title>'),
    # Hero badge
    ('<div class="hero-badge mb-3" data-aos="fade-down" data-aos-delay="100">\n            <i class="fa-solid fa-star"></i> India\'s Biggest Alliance Market\n          </div>',
     '<div class="hero-badge mb-3" data-aos="fade-down" data-aos-delay="100">\n            <i class="fa-solid fa-star"></i><span data-en=" India\'s Biggest Alliance Market" data-hi=" भारत का सबसे बड़ा एलायंस मार्केट"> India\'s Biggest Alliance Market</span>\n          </div>'),
    # Hero CTA
    ('<a href="join.html" class="btn btn-primary-hero pulse-btn">\n              <i class="fa-solid fa-handshake me-2"></i>Join Alliance\n            </a>',
     '<a href="join.html" class="btn btn-primary-hero pulse-btn">\n              <i class="fa-solid fa-handshake me-2"></i><span data-en="Join Alliance" data-hi="अलायंस से जुड़ें">Join Alliance</span>\n            </a>'),
    ('<a href="#" class="btn btn-outline-hero" data-bs-toggle="modal" data-bs-target="#videoModal">\n              <span class="play-icon"><i class="fa-solid fa-play"></i></span> Watch Video\n            </a>',
     '<a href="#" class="btn btn-outline-hero" data-bs-toggle="modal" data-bs-target="#videoModal">\n              <span class="play-icon"><i class="fa-solid fa-play"></i></span><span data-en=" Watch Video" data-hi=" वीडियो देखें"> Watch Video</span>\n            </a>'),
    # Hero subheading
    ('<p class="hero-subheading" data-aos="fade-up" data-aos-delay="400">\n            हमारा लक्ष्य: <strong>1 करोड़ परिवार</strong> को आर्थिक सुरक्षा\n          </p>',
     '<p class="hero-subheading" data-en="Our Goal: Economic security for &lt;strong&gt;1 Crore Families&lt;/strong&gt;" data-hi="हमारा लक्ष्य: &lt;strong&gt;1 करोड़ परिवार&lt;/strong&gt; को आर्थिक सुरक्षा" data-aos="fade-up" data-aos-delay="400">\n            हमारा लक्ष्य: <strong>1 करोड़ परिवार</strong> को आर्थिक सुरक्षा\n          </p>'),
    # Hero vision
    ('<p class="hero-vision" data-aos="fade-up" data-aos-delay="500">\n            <i class="fa-solid fa-eye me-2"></i>विजन 2030: भारत का सबसे बड़ा एग्रीगेटर प्लेटफॉर्म\n          </p>',
     '<p class="hero-vision" data-aos="fade-up" data-aos-delay="500">\n            <i class="fa-solid fa-eye me-2"></i><span data-en="Vision 2030: India\'s Biggest Aggregator Platform" data-hi="विजन 2030: भारत का सबसे बड़ा एग्रीगेटर प्लेटफॉर्म">विजन 2030: भारत का सबसे बड़ा एग्रीगेटर प्लेटफॉर्म</span>\n          </p>'),
    # Stat labels
    ('<div class="stat-label">Businesses</div>', '<div class="stat-label" data-en="Businesses" data-hi="व्यवसाय">Businesses</div>'),
    ('<div class="stat-label">Target Families</div>', '<div class="stat-label" data-en="Target Families" data-hi="लक्षित परिवार">Target Families</div>'),
    ('<div class="stat-label">Vision Year</div>', '<div class="stat-label" data-en="Vision Year" data-hi="विजन वर्ष">Vision Year</div>'),
    ('<div class="stat-label">Join @ Just</div>', '<div class="stat-label" data-en="Join @ Just" data-hi="सिर्फ इतने में जुड़ें">Join @ Just</div>'),
    # Scroll hint
    ('<span>Scroll Down</span>', '<span data-en="Scroll Down" data-hi="नीचे स्क्रॉल करें">Scroll Down</span>'),
    # Section eyebrows
    ('<span class="section-eyebrow" data-aos="fade-down">The MLM Reality Check</span>',
     '<span class="section-eyebrow" data-en="The MLM Reality Check" data-hi="MLM की सच्चाई" data-aos="fade-down">The MLM Reality Check</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">The Revolution</span>',
     '<span class="section-eyebrow" data-en="The Revolution" data-hi="क्रांति" data-aos="fade-down">The Revolution</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">101 Business Empire</span>',
     '<span class="section-eyebrow" data-en="101 Business Empire" data-hi="101 बिजनेस साम्राज्य" data-aos="fade-down">101 Business Empire</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Passive Income 24/7</span>',
     '<span class="section-eyebrow" data-en="Passive Income 24/7" data-hi="पैसिव इनकम 24/7" data-aos="fade-down">Passive Income 24/7</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">The Magic Formula</span>',
     '<span class="section-eyebrow" data-en="The Magic Formula" data-hi="जादुई फॉर्मूला" data-aos="fade-down">The Magic Formula</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">The Big Difference</span>',
     '<span class="section-eyebrow" data-en="The Big Difference" data-hi="बड़ा अंतर" data-aos="fade-down">The Big Difference</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Trust &amp; Transparency</span>',
     '<span class="section-eyebrow" data-en="Trust &amp; Transparency" data-hi="विश्वास और पारदर्शिता" data-aos="fade-down">Trust &amp; Transparency</span>'),
    ('<span class="section-eyebrow">Generational Wealth</span>',
     '<span class="section-eyebrow" data-en="Generational Wealth" data-hi="पीढ़ीगत संपत्ति">Generational Wealth</span>'),
    ('<span class="section-eyebrow">Digital Leadership</span>',
     '<span class="section-eyebrow" data-en="Digital Leadership" data-hi="डिजिटल नेतृत्व">Digital Leadership</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Rewards &amp; Recognition</span>',
     '<span class="section-eyebrow" data-en="Rewards &amp; Recognition" data-hi="पुरस्कार और पहचान" data-aos="fade-down">Rewards &amp; Recognition</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">The Roadmap</span>',
     '<span class="section-eyebrow" data-en="The Roadmap" data-hi="रोडमैप" data-aos="fade-down">The Roadmap</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Transparent Answers</span>',
     '<span class="section-eyebrow" data-en="Transparent Answers" data-hi="पारदर्शी जवाब" data-aos="fade-down">Transparent Answers</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Investment Plans</span>',
     '<span class="section-eyebrow" data-en="Investment Plans" data-hi="निवेश योजनाएं" data-aos="fade-down">Investment Plans</span>'),
    # Diff card titles
    ('<h4>100% सुरक्षित</h4>', '<h4 data-en="100% Safe" data-hi="100% सुरक्षित">100% सुरक्षित</h4>'),
    ('<h4>Alliance Power</h4>', '<h4 data-en="Alliance Power" data-hi="एलायंस की शक्ति">Alliance Power</h4>'),
    ('<h4>Digital System</h4>', '<h4 data-en="Digital System" data-hi="डिजिटल सिस्टम">Digital System</h4>'),
    # Trust badges
    ('<span>Govt Registered</span>', '<span data-en="Govt Registered" data-hi="सरकारी पंजीकृत">Govt Registered</span>'),
    ('<span>Legal Compliant</span>', '<span data-en="Legal Compliant" data-hi="कानूनी अनुपालन">Legal Compliant</span>'),
    ('<span>Transparent System</span>', '<span data-en="Transparent System" data-hi="पारदर्शी प्रणाली">Transparent System</span>'),
    ('<span>Secure Platform</span>', '<span data-en="Secure Platform" data-hi="सुरक्षित प्लेटफॉर्म">Secure Platform</span>'),
    # Vision timeline
    ('<h4>Foundation</h4>', '<h4 data-en="Foundation" data-hi="नींव">Foundation</h4>'),
    ('<h4>Expansion</h4>', '<h4 data-en="Expansion" data-hi="विस्तार">Expansion</h4>'),
    ('<h4>Dominance</h4>', '<h4 data-en="Dominance" data-hi="वर्चस्व">Dominance</h4>'),
    # Pricing eyebrow section already covered, CTA
    ('<a href="join.html" class="btn hpc-cta pulse-btn">\n              <i class="fa-solid fa-rocket me-2"></i>अभी Join करें — सिर्फ ₹4,999\n            </a>',
     '<a href="join.html" class="btn hpc-cta pulse-btn">\n              <i class="fa-solid fa-rocket me-2"></i><span data-en="Join Now — Just ₹4,999" data-hi="अभी Join करें — सिर्फ ₹4,999">अभी Join करें — सिर्फ ₹4,999</span>\n            </a>'),
    # Modal title
    ('<h5 class="modal-title">TSLGC – Alliance Market</h5>',
     '<h5 class="modal-title" data-en="TSLGC – Alliance Market" data-hi="TSLGC – एलायंस मार्केट">TSLGC – Alliance Market</h5>'),
]
fix(os.path.join(ROOT, "index.html"), INDEX)

# ── ABOUT.HTML specific ──────────────────────────────────────
ABOUT = [
    # Page hero
    ('<span class="page-hero-eyebrow" data-aos="fade-down">Our Story</span>',
     '<span class="page-hero-eyebrow" data-en="Our Story" data-hi="हमारी कहानी" data-aos="fade-down">Our Story</span>'),
    # Section eyebrows (about page)
    ('<span class="section-eyebrow" data-aos="fade-down">Our Mission</span>',
     '<span class="section-eyebrow" data-en="Our Mission" data-hi="हमारा मिशन" data-aos="fade-down">Our Mission</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Our Vision</span>',
     '<span class="section-eyebrow" data-en="Our Vision" data-hi="हमारा विजन" data-aos="fade-down">Our Vision</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Why Choose Us</span>',
     '<span class="section-eyebrow" data-en="Why Choose Us" data-hi="हमें क्यों चुनें" data-aos="fade-down">Why Choose Us</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Our Journey</span>',
     '<span class="section-eyebrow" data-en="Our Journey" data-hi="हमारी यात्रा" data-aos="fade-down">Our Journey</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Leadership</span>',
     '<span class="section-eyebrow" data-en="Leadership" data-hi="नेतृत्व" data-aos="fade-down">Leadership</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">The Roadmap</span>',
     '<span class="section-eyebrow" data-en="The Roadmap" data-hi="रोडमैप" data-aos="fade-down">The Roadmap</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Join the Revolution</span>',
     '<span class="section-eyebrow" data-en="Join the Revolution" data-hi="क्रांति में शामिल हों" data-aos="fade-down">Join the Revolution</span>'),
    # Trust badges (also appear on about page)
    ('<span>Govt Registered</span>', '<span data-en="Govt Registered" data-hi="सरकारी पंजीकृत">Govt Registered</span>'),
    ('<span>Legal Compliant</span>', '<span data-en="Legal Compliant" data-hi="कानूनी अनुपालन">Legal Compliant</span>'),
    ('<span>Transparent System</span>', '<span data-en="Transparent System" data-hi="पारदर्शी प्रणाली">Transparent System</span>'),
    ('<span>Secure Platform</span>', '<span data-en="Secure Platform" data-hi="सुरक्षित प्लेटफॉर्म">Secure Platform</span>'),
    # Vision timeline
    ('<h4>Foundation</h4>', '<h4 data-en="Foundation" data-hi="नींव">Foundation</h4>'),
    ('<h4>Expansion</h4>', '<h4 data-en="Expansion" data-hi="विस्तार">Expansion</h4>'),
    ('<h4>Dominance</h4>', '<h4 data-en="Dominance" data-hi="वर्चस्व">Dominance</h4>'),
    # CTA join button
    ('<i class="fa-solid fa-handshake me-2"></i>Join TSLGC Alliance',
     '<i class="fa-solid fa-handshake me-2"></i><span data-en="Join TSLGC Alliance" data-hi="TSLGC अलायंस से जुड़ें">Join TSLGC Alliance</span>'),
]
fix(os.path.join(ROOT, "about.html"), ABOUT)

# ── BUSINESSES.HTML specific ──────────────────────────────────────
BUSINESSES = [
    ('<span class="page-hero-eyebrow" data-aos="fade-down">Our Empire</span>',
     '<span class="page-hero-eyebrow" data-en="Our Empire" data-hi="हमारा साम्राज्य" data-aos="fade-down">Our Empire</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">101 Business Alliance</span>',
     '<span class="section-eyebrow" data-en="101 Business Alliance" data-hi="101 बिजनेस एलायंस" data-aos="fade-down">101 Business Alliance</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Sector Strength</span>',
     '<span class="section-eyebrow" data-en="Sector Strength" data-hi="सेक्टर की ताकत" data-aos="fade-down">Sector Strength</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Join the Alliance</span>',
     '<span class="section-eyebrow" data-en="Join the Alliance" data-hi="एलायंस से जुड़ें" data-aos="fade-down">Join the Alliance</span>'),
    ('<i class="fa-solid fa-handshake me-2"></i>Join TSLGC Alliance',
     '<i class="fa-solid fa-handshake me-2"></i><span data-en="Join TSLGC Alliance" data-hi="TSLGC अलायंस से जुड़ें">Join TSLGC Alliance</span>'),
]
fix(os.path.join(ROOT, "businesses.html"), BUSINESSES)

# ── INCOME.HTML specific ──────────────────────────────────────
INCOME = [
    ('<span class="page-hero-eyebrow" data-aos="fade-down">Income System</span>',
     '<span class="page-hero-eyebrow" data-en="Income System" data-hi="इनकम सिस्टम" data-aos="fade-down">Income System</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Alliance Income</span>',
     '<span class="section-eyebrow" data-en="Alliance Income" data-hi="एलायंस इनकम" data-aos="fade-down">Alliance Income</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">The Magic Formula</span>',
     '<span class="section-eyebrow" data-en="The Magic Formula" data-hi="जादुई फॉर्मूला" data-aos="fade-down">The Magic Formula</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Income Comparison</span>',
     '<span class="section-eyebrow" data-en="Income Comparison" data-hi="इनकम तुलना" data-aos="fade-down">Income Comparison</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Rewards &amp; Recognition</span>',
     '<span class="section-eyebrow" data-en="Rewards &amp; Recognition" data-hi="पुरस्कार और पहचान" data-aos="fade-down">Rewards &amp; Recognition</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Start Earning</span>',
     '<span class="section-eyebrow" data-en="Start Earning" data-hi="कमाई शुरू करें" data-aos="fade-down">Start Earning</span>'),
    ('<i class="fa-solid fa-handshake me-2"></i>Join TSLGC Alliance',
     '<i class="fa-solid fa-handshake me-2"></i><span data-en="Join TSLGC Alliance" data-hi="TSLGC अलायंस से जुड़ें">Join TSLGC Alliance</span>'),
]
fix(os.path.join(ROOT, "income.html"), INCOME)

# ── PRICING.HTML specific ──────────────────────────────────────
PRICING = [
    ('<span class="page-hero-eyebrow" data-aos="fade-down">Investment Plans</span>',
     '<span class="page-hero-eyebrow" data-en="Investment Plans" data-hi="निवेश योजनाएं" data-aos="fade-down">Investment Plans</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Full Breakdown</span>',
     '<span class="section-eyebrow" data-en="Full Breakdown" data-hi="पूरी जानकारी" data-aos="fade-down">Full Breakdown</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Our Guarantee</span>',
     '<span class="section-eyebrow" data-en="Our Guarantee" data-hi="हमारी गारंटी" data-aos="fade-down">Our Guarantee</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Frequently Asked</span>',
     '<span class="section-eyebrow" data-en="Frequently Asked" data-hi="अक्सर पूछे जाने वाले" data-aos="fade-down">Frequently Asked</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Ready to Start?</span>',
     '<span class="section-eyebrow" data-en="Ready to Start?" data-hi="शुरू करने के लिए तैयार?" data-aos="fade-down">Ready to Start?</span>'),
    ('<i class="fa-solid fa-rocket me-2"></i>Join Now — ₹4,999',
     '<i class="fa-solid fa-rocket me-2"></i><span data-en="Join Now — ₹4,999" data-hi="अभी जुड़ें — ₹4,999">Join Now — ₹4,999</span>'),
]
fix(os.path.join(ROOT, "pricing.html"), PRICING)

# ── FAQ.HTML specific ──────────────────────────────────────
FAQ = [
    ('<span class="page-hero-eyebrow" data-aos="fade-down">Got Questions?</span>',
     '<span class="page-hero-eyebrow" data-en="Got Questions?" data-hi="सवाल हैं?" data-aos="fade-down">Got Questions?</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">General Questions</span>',
     '<span class="section-eyebrow" data-en="General Questions" data-hi="सामान्य प्रश्न" data-aos="fade-down">General Questions</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Income &amp; Business</span>',
     '<span class="section-eyebrow" data-en="Income &amp; Business" data-hi="इनकम और व्यवसाय" data-aos="fade-down">Income &amp; Business</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Legal &amp; Trust</span>',
     '<span class="section-eyebrow" data-en="Legal &amp; Trust" data-hi="कानूनी और विश्वास" data-aos="fade-down">Legal &amp; Trust</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Still Have Questions?</span>',
     '<span class="section-eyebrow" data-en="Still Have Questions?" data-hi="अभी भी सवाल हैं?" data-aos="fade-down">Still Have Questions?</span>'),
]
fix(os.path.join(ROOT, "faq.html"), FAQ)

# ── CONTACT.HTML specific ──────────────────────────────────────
CONTACT = [
    ('<span class="page-hero-eyebrow" data-aos="fade-down">Get In Touch</span>',
     '<span class="page-hero-eyebrow" data-en="Get In Touch" data-hi="संपर्क करें" data-aos="fade-down">Get In Touch</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Our Location</span>',
     '<span class="section-eyebrow" data-en="Our Location" data-hi="हमारा स्थान" data-aos="fade-down">Our Location</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Send Us a Message</span>',
     '<span class="section-eyebrow" data-en="Send Us a Message" data-hi="हमें संदेश भेजें" data-aos="fade-down">Send Us a Message</span>'),
    # Form labels
    ('<label for="contactName">Full Name *</label>',
     '<label for="contactName" data-en="Full Name *" data-hi="पूरा नाम *">Full Name *</label>'),
    ('<label for="contactPhone">Phone Number *</label>',
     '<label for="contactPhone" data-en="Phone Number *" data-hi="फोन नंबर *">Phone Number *</label>'),
    ('<label for="contactEmail">Email Address</label>',
     '<label for="contactEmail" data-en="Email Address" data-hi="ईमेल पता">Email Address</label>'),
    ('<label for="contactSubject">Subject *</label>',
     '<label for="contactSubject" data-en="Subject *" data-hi="विषय *">Subject *</label>'),
    ('<label for="contactMsg">Message *</label>',
     '<label for="contactMsg" data-en="Message *" data-hi="संदेश *">Message *</label>'),
]
fix(os.path.join(ROOT, "contact.html"), CONTACT)

# ── JOIN.HTML specific ──────────────────────────────────────
JOIN = [
    ('<span class="page-hero-eyebrow" data-aos="fade-down">Start Your Journey</span>',
     '<span class="page-hero-eyebrow" data-en="Start Your Journey" data-hi="अपनी यात्रा शुरू करें" data-aos="fade-down">Start Your Journey</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Why Join?</span>',
     '<span class="section-eyebrow" data-en="Why Join?" data-hi="क्यों जुड़ें?" data-aos="fade-down">Why Join?</span>'),
    ('<span class="section-eyebrow" data-aos="fade-down">Registration Form</span>',
     '<span class="section-eyebrow" data-en="Registration Form" data-hi="पंजीकरण फॉर्म" data-aos="fade-down">Registration Form</span>'),
    # Form labels
    ('<label for="regName">Full Name *</label>',
     '<label for="regName" data-en="Full Name *" data-hi="पूरा नाम *">Full Name *</label>'),
    ('<label for="regPhone">Phone Number *</label>',
     '<label for="regPhone" data-en="Phone Number *" data-hi="फोन नंबर *">Phone Number *</label>'),
    ('<label for="regEmail">Email Address</label>',
     '<label for="regEmail" data-en="Email Address" data-hi="ईमेल पता">Email Address</label>'),
    ('<label for="regCity">City *</label>',
     '<label for="regCity" data-en="City *" data-hi="शहर *">City *</label>'),
    ('<label for="regSponsor">Sponsor ID (Optional)</label>',
     '<label for="regSponsor" data-en="Sponsor ID (Optional)" data-hi="स्पॉन्सर ID (वैकल्पिक)">Sponsor ID (Optional)</label>'),
    ('<label for="regPlan">Select Plan *</label>',
     '<label for="regPlan" data-en="Select Plan *" data-hi="प्लान चुनें *">Select Plan *</label>'),
    ('<label for="regMsg">Message / Query</label>',
     '<label for="regMsg" data-en="Message / Query" data-hi="संदेश / प्रश्न">Message / Query</label>'),
]
fix(os.path.join(ROOT, "join.html"), JOIN)

print("\nAll done!")
