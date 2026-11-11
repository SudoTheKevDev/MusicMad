// ============================================================
//  MusicMad – Vista previa en vivo de "Mi perfil"
//  js/perfil.js
//
//  Basado en la lógica de la actividad 1.4 (generador de
//  perfiles), pero SIN el bloque de autocompletado por API de
//  GitHub: aquí solo se eligen avatares predefinidos guardados
//  en el propio servidor y los datos se guardan en la base de
//  datos de MusicMad (ver perfil.php / includes/auth.php).
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
  const dom = {
    bio: document.getElementById('input-bio'),
    color: document.getElementById('input-color'),
    avatarInput: document.getElementById('input-avatar'),
    avatars: document.getElementById('avatar-selector'),

    previewName: document.getElementById('name-preview'),
    previewRole: document.getElementById('role-preview'),
    previewBio: document.getElementById('bio-preview'),
    previewAvatar: document.getElementById('avatar-preview'),
    previewHeader: document.getElementById('preview-header'),
  };

  if (!dom.avatars) return; // La página no tiene el formulario de perfil

  function renderCard() {
    dom.previewBio.textContent = dom.bio.value || 'Aún no escribes una biografía...';
    dom.previewAvatar.src = dom.avatarInput.value;
    dom.previewHeader.style.backgroundColor = dom.color.value;

    document.querySelectorAll('.avatar-option').forEach((img) => {
      img.classList.toggle('selected', img.dataset.url === dom.avatarInput.value);
    });
  }

  document.querySelectorAll('.avatar-option').forEach((img) => {
    img.addEventListener('click', () => {
      dom.avatarInput.value = img.dataset.url;
      renderCard();
    });
  });

  dom.bio.addEventListener('input', renderCard);
  dom.color.addEventListener('input', renderCard);

  renderCard();
});
