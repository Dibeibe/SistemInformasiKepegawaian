<!-- includes/footer.php -->
<footer class="site-footer">
  <div class="container footer-inner">
    <div class="footer-left">
      © 2025 <strong>Sistem Kepegawaian</strong>
      <span class="dot">•</span>
      Project Kelompok 5
    </div>

    <div class="footer-right">
      <span>Adib Praditya</span>
      <span class="sep">•</span>
      <span>Cindy Bela</span>
      <span class="sep">•</span>
      <span>Verry Ferdian</span>
      <span class="sep">•</span>
      <span>Yuni Rubieanti</span>
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
:root{
  /* Samain sama login/dashboard */
  --grad-a:#667eea;
  --grad-b:#764ba2;

  --glass: rgba(255,255,255,0.14);
  --stroke: rgba(255,255,255,0.22);

  --text:#ffffff;
  --text-soft: rgba(255,255,255,0.85);
  --muted: rgba(255,255,255,0.70);

  --shadow: 0 -12px 26px rgba(0,0,0,0.18);
  --radius: 18px;
}

/* ===== Footer ===== */
.site-footer{
  margin-top: 40px;
  padding: 14px 16px 18px;
  color: var(--text);
}

/* Footer “bar” */
.footer-inner{
  max-width: 1100px;
  margin: auto;
  padding: 14px 16px;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 10px;

  background: linear-gradient(135deg, var(--grad-a), var(--grad-b));
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  position: relative;
  overflow: hidden;
}

/* Glass highlight */
.footer-inner::before{
  content:"";
  position:absolute;
  inset:-40% -20% auto -20%;
  height: 140%;
  background: radial-gradient(closest-side, rgba(255,255,255,0.20), rgba(255,255,255,0.00));
  transform: rotate(12deg);
  pointer-events:none;
  opacity: .9;
}

.footer-left, .footer-right{
  position: relative; /* biar di atas highlight */
  z-index: 1;
  font-size: 14px;
}

.footer-left{
  color: var(--text-soft);
}
.footer-left strong{
  color: #fff;
  font-weight: 800;
}

.footer-right{
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  color: var(--muted);
}

.footer-right span{
  transition: .2s ease;
}
.footer-right span:not(.sep):hover{
  color:#fff;
  transform: translateY(-1px);
}

.sep{
  opacity: .65;
}

.dot{
  margin: 0 6px;
  opacity: .7;
}

/* ===== Responsive ===== */
@media (max-width: 768px){
  .footer-inner{
    flex-direction: column;
    text-align: center;
  }
}
</style>
