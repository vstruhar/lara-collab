export const isImage = (file) => {
  return ["image/jpeg", "image/gif", "image/png", "image/svg", "image/webp", ".jpeg", ".jpg", ".gif", ".png", ".svg", ".webp", ".bmp"].some(
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
