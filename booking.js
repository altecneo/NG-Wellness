/* ═══════════════════════════════════════════
   Beauty for Broken Wellness — Site JS
   ═══════════════════════════════════════════ */

// ─── PAGE NAVIGATION ───────────────────────
function showPage(name) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.getElementById('page-' + name).classList.add('active');
  document.querySelectorAll('.nav-links a[data-page]').forEach(a => {
    a.classList.toggle('active', a.dataset.page === name);
  });
  document.getElementById('navLinks').classList.remove('open');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function download(fileName) {
    // 1. Create a hidden anchor element
    const link = document.createElement('a');
    
    // 2. Set the path to your file (update the folder path if needed)
    link.href = fileName; 
    
    // 3. Force the download and set the saved file name
    link.download = fileName; 
    
    // 4. Append, click, and clean up
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}



function toggleMenu() {
  document.getElementById('navLinks').classList.toggle('open');
}

function toggleFaq(btn) {
  btn.closest('.faq-item').classList.toggle('open');
}

function handleFormSubmit(btn) {
  btn.textContent = 'Message Sent! ✓';
  btn.style.background = 'var(--sage-mid)';
  btn.disabled = true;
}

window.addEventListener('scroll', () => {
  document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 20);
});

// Set initial active nav link
document.addEventListener('DOMContentLoaded', () => {
  const homeLink = document.querySelector('[data-page="home"]');
  if (homeLink) homeLink.classList.add('active');
});


// ─── BOOKING SYSTEM ────────────────────────

const booking = {
  service: null, price: null, duration: null,
  date: null, time: null,
  fname: '', lname: '', email: '', phone: ''
};

let calYear, calMonth;

function openBooking(preservice) {
  document.getElementById('bookingOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
  // Reset to step 1 if reopening
  document.getElementById('bookingProgress').style.display = 'flex';
  goToStep(1);
  initCalendar();
  if (preservice) {
    document.querySelectorAll('.bsvc-item').forEach(item => {
      const h4 = item.querySelector('h4');
      if (h4 && h4.textContent.includes(preservice)) item.click();
    });
  }
}

function closeBooking() {
  document.getElementById('bookingOverlay').classList.remove('open');
  document.body.style.overflow = '';
}

function closeBookingOnOverlay(e) {
  if (e.target === document.getElementById('bookingOverlay')) closeBooking();
}

function goToStep(n) {
  document.querySelectorAll('.booking-step').forEach(s => s.classList.remove('active'));
  document.getElementById('bstep-' + n).classList.add('active');

  for (let i = 1; i <= 4; i++) {
    const prog = document.getElementById('prog-' + i);
    const dot  = prog.querySelector('.bprog-dot');
    const line = document.getElementById('pline-' + i);
    prog.classList.remove('active', 'done');
    dot.classList.remove('active', 'done');
    if (i < n)      { prog.classList.add('done');   dot.classList.add('done');   dot.textContent = '✓'; }
    else if (i === n){ prog.classList.add('active'); dot.classList.add('active'); dot.textContent = i; }
    else             { dot.textContent = i; }
    if (line) line.classList.toggle('done', i < n);
  }

  if (n === 4) fillConfirm();
  document.getElementById('bookingModal').scrollTop = 0;
}

// ── Step 1: Service ──────────────────────────
function selectService(el, name, price, duration) {
  document.querySelectorAll('.bsvc-item').forEach(i => i.classList.remove('selected'));
  el.classList.add('selected');
  booking.service = name; booking.price = price; booking.duration = duration;
  const btn = document.getElementById('step1Next');
  btn.disabled = false; btn.style.opacity = '1'; btn.style.cursor = 'pointer';
}

// ── Step 2: Calendar ────────────────────────
function initCalendar() {
  const now = new Date();
  calYear = now.getFullYear(); calMonth = now.getMonth();
  renderCalendar();
}

function changeMonth(dir) {
  calMonth += dir;
  if (calMonth > 11) { calMonth = 0; calYear++; }
  if (calMonth < 0)  { calMonth = 11; calYear--; }
  renderCalendar();
}

const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

function renderCalendar() {
  document.getElementById('calMonthLabel').textContent = MONTHS[calMonth] + ' ' + calYear;
  const grid = document.getElementById('calGrid');
  grid.querySelectorAll('.bcal-day').forEach(el => el.remove());

  const firstDay     = new Date(calYear, calMonth, 1).getDay();
  const daysInMonth  = new Date(calYear, calMonth + 1, 0).getDate();
  const today        = new Date();
  const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());

  for (let i = 0; i < firstDay; i++) {
    const e = document.createElement('div');
    e.className = 'bcal-day empty';
    grid.appendChild(e);
  }

  for (let d = 1; d <= daysInMonth; d++) {
    const cell      = document.createElement('div');
    const thisDate  = new Date(calYear, calMonth, d);
    const dow       = thisDate.getDay();
    const isPast    = thisDate < todayMidnight;
    const isSun     = dow === 0;
    const isSat     = dow === 6;
    const dateStr   = `${calYear}-${String(calMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const isToday   = (d === today.getDate() && calMonth === today.getMonth() && calYear === today.getFullYear());

    cell.className  = 'bcal-day';
    cell.textContent = d;

    if (isPast || isSun) {
      cell.classList.add('disabled');
    } else {
      if (isToday) cell.classList.add('today');
      if (booking.date === dateStr) cell.classList.add('selected');
      cell.onclick = () => selectDate(dateStr, cell, MONTHS[calMonth] + ' ' + d + ', ' + calYear, isSat);
    }
    grid.appendChild(cell);
  }
}

function selectDate(dateStr, cell, label, isSat) {
  document.querySelectorAll('.bcal-day').forEach(c => c.classList.remove('selected'));
  cell.classList.add('selected');
  booking.date = dateStr; booking.time = null;
  setStep2Next(false);
  renderTimeSlots(isSat, label);
}

function renderTimeSlots(isSat, dateLabel) {
  document.getElementById('timeLabel').textContent = 'Available times — ' + dateLabel;
  const list = document.getElementById('timeList');
  list.innerHTML = '';

  const allSlots  = isSat
    ? ['9:00 AM', '10:00 AM', '11:00 AM', '12:00 PM']
    : ['9:00 AM', '10:00 AM', '11:00 AM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM'];
  const unavail   = new Set([allSlots[1], allSlots[allSlots.length - 1]]);

  allSlots.forEach(t => {
    const el = document.createElement('div');
    el.className = 'btime-item' + (unavail.has(t) ? ' unavailable' : '');
    el.textContent = t;
    if (!unavail.has(t)) el.onclick = () => selectTime(t, el);
    list.appendChild(el);
  });

  document.getElementById('timeNote').textContent = isSat
    ? 'Saturday hours: 9 AM – 12 PM ET'
    : 'Monday – Friday: 9 AM – 4 PM ET';
}

function selectTime(t, el) {
  document.querySelectorAll('.btime-item').forEach(i => i.classList.remove('selected'));
  el.classList.add('selected');
  booking.time = t;
  setStep2Next(true);
}

function setStep2Next(enabled) {
  const btn = document.getElementById('step2Next');
  btn.disabled      = !enabled;
  btn.style.opacity = enabled ? '1' : '0.4';
  btn.style.cursor  = enabled ? 'pointer' : 'not-allowed';
}

// ── Step 3: Info validation ──────────────────
function validateStep3() {
  const ok = document.getElementById('bf-fname').value.trim() &&
             document.getElementById('bf-lname').value.trim() &&
             document.getElementById('bf-email').value.includes('@') &&
             document.getElementById('bf-phone').value.trim().length >= 10;
  const btn = document.getElementById('step3Next');
  btn.disabled      = !ok;
  btn.style.opacity = ok ? '1' : '0.4';
  btn.style.cursor  = ok ? 'pointer' : 'not-allowed';
}

// ── Step 4: Confirm ──────────────────────────
function fillConfirm() {
  let dateLabel = '—';
  if (booking.date) {
    const [y, m, d] = booking.date.split('-');
    dateLabel = MONTHS[parseInt(m) - 1] + ' ' + parseInt(d) + ', ' + y;
  }
  document.getElementById('confirm-service').textContent  = booking.service;
  document.getElementById('confirm-date').textContent     = dateLabel;
  document.getElementById('confirm-time').textContent     = (booking.time || '—') + ' ET';
  document.getElementById('confirm-duration').textContent = booking.duration;
  document.getElementById('confirm-price').textContent    = '$' + booking.price;
  document.getElementById('confirm-name').textContent     =
    (document.getElementById('bf-fname').value + ' ' + document.getElementById('bf-lname').value).trim();
  document.getElementById('confirm-email').textContent    = document.getElementById('bf-email').value;
  document.getElementById('confirm-phone').textContent    = document.getElementById('bf-phone').value;
}

function confirmBooking() {
  let dateLabel = '';
  if (booking.date) {
    const [y, m, d] = booking.date.split('-');
    dateLabel = MONTHS[parseInt(m) - 1] + ' ' + parseInt(d) + ', ' + y;
  }
  const name = document.getElementById('bf-fname').value;
  document.getElementById('success-msg').textContent =
    `Hi ${name}! Your ${booking.service} appointment request for ${dateLabel} at ${booking.time} ET has been received. Julie will send a confirmation email within 24 hours.`;

  document.getElementById('bookingProgress').style.display = 'none';
  // Show success step
  document.querySelectorAll('.booking-step').forEach(s => s.classList.remove('active'));
  document.getElementById('bstep-5').classList.add('active');
  document.getElementById('bookingModal').scrollTop = 0;
}

// ESC to close
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeBooking(); });
