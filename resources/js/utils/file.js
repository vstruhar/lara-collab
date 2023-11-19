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
