export const ATTACHMENT_MAX_BYTES = 20 * 1024 * 1024;

export const ACCEPTED_ATTACHMENT_TYPES = [
  "image/jpeg",
  "image/png",
  "image/gif",
  "image/webp",
  "image/bmp",
  "application/pdf",
  "text/plain",
  "text/csv",
  "application/msword",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  "application/vnd.ms-excel",
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
  "application/vnd.ms-powerpoint",
  "application/vnd.openxmlformats-officedocument.presentationml.presentation",
  "application/zip",
  "application/x-zip-compressed",
  "video/mp4",
  "video/webm",
  "audio/mpeg",
  "audio/wav",
  "audio/wave",
  "audio/ogg",
];

export const isImage = (file) => {
  return ["image/jpeg", "image/gif", "image/png", "image/webp", "image/bmp", ".jpeg", ".jpg", ".gif", ".png", ".webp", ".bmp"].some(
    (type) => file.type.includes(type) || file.name.includes(type),
  );
};

export const isViewable = (file) => {
  const video = [".mp4", ".ogg", ".webm"];
  const audio = [".mp3", ".wav", ".ogg", ".wma"];
  const document = [".pdf"];

  const viewable = [...video, ...audio, ...document];

  return viewable.some(
    (type) => file.type.includes(type) || file.name.includes(type),
  );
};

export const download = (data, filename, mime, bom) => {
  var blobData = (typeof bom !== 'undefined') ? [bom, data] : [data]
  var blob = new Blob(blobData, {type: mime || 'application/octet-stream'});
  if (typeof window.navigator.msSaveBlob !== 'undefined') {
      window.navigator.msSaveBlob(blob, filename);
  } else {
      var blobURL = (window.URL && window.URL.createObjectURL) ? window.URL.createObjectURL(blob) : window.webkitURL.createObjectURL(blob);
      var tempLink = document.createElement('a');
      tempLink.style.display = 'none';
      tempLink.href = blobURL;
      tempLink.setAttribute('download', filename);

      if (typeof tempLink.download === 'undefined') {
          tempLink.setAttribute('target', '_blank');
      }
      document.body.appendChild(tempLink);
      tempLink.click();

      setTimeout(function() {
          document.body.removeChild(tempLink);
          window.URL.revokeObjectURL(blobURL);
      }, 200)
  }
}
