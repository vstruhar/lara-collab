import dayjs from "dayjs";
import JsFileDownloader from "js-file-downloader";

export const isOverdue = (task) => {
  return dayjs().isAfter(task.due_on);
};

export const openAttachment = (file) => {
  const image = ["image/", ".webp", ".png", ".svg", ".bmp"];
  const video = [".mp4", ".ogg", ".webm"];
  const audio = [".mp3", ".wav", ".ogg", ".wma"];
  const document = [".pdf"];

  const viewable = [...image, ...video, ...audio, ...document];
  if (
    viewable.some(
      (type) => file.type.includes(type) || file.name.includes(type),
    )
  ) {
    window.open(file.path, "_blank");
  } else {
    new JsFileDownloader({
      url: file.path,
      filename: file.name,
      contentType: file.type,
      nativeFallbackOnError: true,
    }).catch((error) => console.error("Failed to download file", error));
  }
};
