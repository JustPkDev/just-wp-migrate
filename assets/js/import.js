const events = ["dragenter", "dragover", "dragleave", "drop"];
const input = jQuery("<input>")
  .attr({
    type: "file",
    multiple: false,
    accept: ".jwm",
  })
  .css("display", "none");

jQuery(document).ready(($) => {
  jQuery("#drag").on(events.join(" "), (e) => {
    e.preventDefault();
    e.stopPropagation();
  });

  jQuery("#drag").on("dragenter dragover", () => {
    jQuery("#drag").css("border", "2px solid #004eaf");
  });

  jQuery("#drag").on("dragleave drop", () => {
    jQuery("#drag").css("border", "2px dotted #004eaf");
  });

  jQuery("#drag").on("drop", (e) => {
    const files = e.originalEvent.dataTransfer.files;
    if (files.length !== 0) {
      const ext = files[0].name.split(".").pop();
      if (ext !== "jwm") {
        Swal.fire({
          icon: "error",
          text: "Only .jwm File Is Allowed!",
        });
        return;
      }

      upload(files[0]);
    }
  });

  jQuery("#drag").click(() => {
    input.appendTo("body").click();
  });

  input.change((e) => {
    const files = e.target.files;
    const ext = files[0].name.split(".").pop();
    if (ext !== "jwm") {
      Swal.fire({
        icon: "error",
        text: "Only .jwm File Is Allowed!",
      });
      input.val("");
      return;
    }

    upload(files[0]);
  });
});

const upload = (file) => {
  Swal.fire({
    title: "Importing Backup",
    html: `
        <div class="d-flex flex-column justify-content-center align-items-center mt-5 mb-5">
            <div class="loader"></div>
            <p class="mt-2">Importing</p>
        </div>`,
    showConfirmButton: false,
    showCloseButton: false,
    showCancelButton: false,
    allowOutsideClick: false,
  });

  const xhr = new XMLHttpRequest();
  xhr.open("POST", jwm_import_ajax.ajax_url, true);

  const formData = new FormData();
  formData.append("action", "jwm_import_file");
  formData.append("nonce", jwm_import_ajax.nonce);
  formData.append("file", file);

  xhr.onload = () => {
    // console.log(xhr.responseText);
    const res = JSON.parse(xhr.responseText);
    if (res.success) {
      Swal.fire({
        icon: "success",
        text: "Imported Successfully.",
      });
      input.val("");
    } else {
      Swal.fire({
        icon: "error",
        text: "Something went wrong!",
      });
      input.val("");
    }
  };

  xhr.send(formData);
};
