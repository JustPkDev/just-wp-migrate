jQuery(document).ready(($) => {
  // Handle dots click
  $(".dots").click((e) => {
    const target = $(e.target);
    const menu = target.closest("td").find(".dropdown-js");

    $(".dots").not(target).data("open", false);
    $(".dropdown-js").addClass("d-none");

    if (target.data("open")) {
      target.data("open", false);
      menu.addClass("d-none");
    } else {
      target.data("open", true);
      menu.removeClass("d-none");
    }

    e.stopPropagation();
  });

  // Close when clicking outside
  $(document).click(() => {
    $(".dots").data("open", false);
    $(".dropdown-js").addClass("d-none");
  });
});

jQuery("#create").click(async () => {
  const result = await Swal.fire({
    title: "Are Your Sure?",
    icon: "warning",
    showConfirmButton: true,
    showCloseButton: true,
    showCancelButton: true,
  });

  if (!result.isConfirmed) return;

  Swal.fire({
    title: "Creating Backup",
    html: `
    <div class="d-flex flex-column justify-content-center align-items-center mt-5 mb-5">
        <div class="loader"></div>
        <p id="res" class="mt-2">Checking...</p>
    </div>`,
    showConfirmButton: false,
    showCloseButton: false,
    showCancelButton: false,
    allowOutsideClick: false,
  });

  const xhr = new XMLHttpRequest();
  xhr.open("POST", jwm_backup_ajax.ajax_url, true);

  const formData = new FormData();
  formData.append("action", "jwm_create_backup");
  formData.append("nonce", jwm_backup_ajax.nonce);

  xhr.onprogress = function () {
    const responseLines = xhr.responseText.split("\n").filter(Boolean);
    const lastLine = responseLines.pop();

    try {
      const progressData = JSON.parse(lastLine);
      if (progressData.progress !== 100) {
        jQuery("#res").text(progressData.status ?? "");
      } else {
        Swal.fire({
          title: "Created Successfully.",
          icon: "success",
          showConfirmButton: true,
          showCloseButton: false,
          showCancelButton: false,
          allowOutsideClick: false,
        }).then((e) => {
          if (e.isConfirmed) location.reload();
        });
      }
    } catch (error) {
      Swal.fire({
        title: "Waiting...",
        text: "Some Thing Went Wrong!",
        icon: "question",
      });
    }
  };

  xhr.send(formData);
});

jQuery(".delete").click(async (e) => {
  const result = await Swal.fire({
    title: "Are Your Sure?",
    icon: "warning",
    showConfirmButton: true,
    showCloseButton: true,
    showCancelButton: true,
  });

  if (!result.isConfirmed) return;

  Swal.fire({
    title: "Deleting Backup",
    html: `
    <div class="d-flex flex-column justify-content-center align-items-center mt-5 mb-5">
        <div class="loader"></div>
        <p class="mt-2">Deleting</p>
    </div>`,
    showConfirmButton: false,
    showCloseButton: false,
    showCancelButton: false,
    allowOutsideClick: false,
  });

  const name = jQuery(e.target).parent().data("name");
  const xhr = new XMLHttpRequest();
  xhr.open("POST", jwm_backup_ajax.ajax_url, true);

  const formData = new FormData();
  formData.append("action", "jwm_delete_file");
  formData.append("nonce", jwm_backup_ajax.nonce);
  formData.append("name", name);

  xhr.onload = () => {
    const res = JSON.parse(xhr.responseText);
    if (res.success) {
      Swal.fire({
        icon: "success",
        text: "Deleted Successfully.",
        showConfirmButton: true,
        showCloseButton: false,
        showCancelButton: false,
        allowOutsideClick: false,
      }).then((e) => {
        if (e.isConfirmed) location.reload();
      });
    } else {
      Swal.fire({
        icon: "error",
        text: "Something went wrong!",
      });
    }
  };

  xhr.send(formData);
});

jQuery(".download").click((e) => {
  const name = jQuery(e.target).parent().data("name");
  const url = jQuery(e.target).data("path") + name;
  const a = jQuery("<a>")
    .attr("href", url)
    .attr("download", name)
    .appendTo("body");

  a[0].click();
  a.remove();
});

jQuery(".restore").click(async (e) => {
  const result = await Swal.fire({
    title: "Are Your Sure?",
    icon: "warning",
    showConfirmButton: true,
    showCloseButton: true,
    showCancelButton: true,
  });

  if (!result.isConfirmed) return;

  Swal.fire({
    title: "Restoring Backup",
    html: `
    <div class="d-flex flex-column justify-content-center align-items-center mt-5 mb-5">
        <div class="loader"></div>
        <p id="res" class="mt-2">Checking...</p>
    </div>`,
    showConfirmButton: false,
    showCloseButton: false,
    showCancelButton: false,
    allowOutsideClick: false,
  });

  const name = jQuery(e.target).parent().data("name");
  const xhr = new XMLHttpRequest();
  xhr.open("POST", jwm_backup_ajax.ajax_url, true);

  const formData = new FormData();
  formData.append("action", "jwm_restore_backup");
  formData.append("nonce", jwm_backup_ajax.nonce);
  formData.append("name", name);

  xhr.onprogress = function () {
    const responseLines = xhr.responseText.split("\n").filter(Boolean);
    const lastLine = responseLines.pop();

    try {
      const progressData = JSON.parse(lastLine);
      if (progressData.progress !== 100) {
        jQuery("#res").text(progressData.status ?? "");
      } else {
        Swal.fire({
          title: "Restore Successfully.",
          icon: "success",
          showConfirmButton: true,
          showCloseButton: false,
          showCancelButton: false,
          allowOutsideClick: false,
        }).then((e) => {
          if (e.isConfirmed) location.reload();
        });
      }
    } catch (error) {
      Swal.fire({
        title: "Waiting...",
        text: "Some Thing Went Wrong!",
        icon: "question",
      });
    }
  };

  xhr.send(formData);
});
