import { isImage } from "@/utils/file";
import { Group, Image, Text, rem } from "@mantine/core";
import {
  IconCircleX,
  IconFile,
  IconFileTypeCss,
  IconFileTypeCsv,
  IconFileTypeDoc,
  IconFileTypeDocx,
  IconFileTypeHtml,
  IconFileTypeJs,
  IconFileTypeJsx,
  IconFileTypePdf,
  IconFileTypePhp,
  IconFileTypeSql,
  IconFileTypeSvg,
  IconFileTypeTs,
  IconFileTypeTxt,
  IconFileTypeVue,
  IconFileTypeXls,
  IconFileTypeXml,
  IconFileTypeZip,
} from "@tabler/icons-react";
import classes from "./css/FileThumbnail.module.css";

export default function FileThumbnail({ file, remove, open, ...props }) {
  const iconProps = { width: rem(45), height: rem(45) };
  let icon = <IconFile style={iconProps} />;

  const alreadyUploaded = file.id !== undefined;

  if (isImage(file)) {
    const imageUrl = alreadyUploaded
      ? file.thumb || file.path
      : URL.createObjectURL(file);
    icon = (
      <Image
        radius="md"
        w={45}
        h={45}
        src={imageUrl}
        onLoad={() => !alreadyUploaded && URL.revokeObjectURL(imageUrl)}
      />
    );
  } else if (file.type.includes("pdf")) {
    icon = <IconFileTypePdf style={iconProps} />;
  } else if (file.type.includes("css")) {
    icon = <IconFileTypeCss style={iconProps} />;
  } else if (file.type.includes("csv")) {
    icon = <IconFileTypeCsv style={iconProps} />;
  } else if (file.type.includes("docx")) {
    icon = <IconFileTypeDocx style={iconProps} />;
  } else if (file.type.includes("doc")) {
    icon = <IconFileTypeDoc style={iconProps} />;
  } else if (file.type.includes("html")) {
    icon = <IconFileTypeHtml style={iconProps} />;
  } else if (file.name.includes(".jsx")) {
    icon = <IconFileTypeJsx style={iconProps} />;
  } else if (file.type.includes("javascript")) {
    icon = <IconFileTypeJs style={iconProps} />;
  } else if (file.type.includes("php")) {
    icon = <IconFileTypePhp style={iconProps} />;
  } else if (file.name.includes(".sql")) {
    icon = <IconFileTypeSql style={iconProps} />;
  } else if (file.type.includes("image/svg")) {
    icon = <IconFileTypeSvg style={iconProps} />;
  } else if (file.type.includes("application/zip")) {
    icon = <IconFileTypeZip style={iconProps} />;
  } else if (file.type === "text/plain") {
    icon = <IconFileTypeTxt style={iconProps} />;
  } else if (file.name.includes(".vue")) {
    icon = <IconFileTypeVue style={iconProps} />;
  } else if (file.type.includes("xls")) {
    icon = <IconFileTypeXls style={iconProps} />;
  } else if (file.type.includes("xml")) {
    icon = <IconFileTypeXml style={iconProps} />;
  } else if (file.name.includes(".ts")) {
    icon = <IconFileTypeTs style={iconProps} />;
  }

  return (
    <Group gap="sm" wrap="nowrap" className={classes.file} {...props}>
      <div className={classes.iconContainer}>
        <div className={classes.icon}>{icon}</div>
        <IconCircleX
          className={classes.remove}
          stroke={1.5}
          onClick={() => remove()}
        />
      </div>
      <div className={classes.text}>
        <Text
          fz={15}
          fw={400}
          truncate="end"
          onClick={() => alreadyUploaded && open()}
          style={{ cursor: alreadyUploaded ? "pointer" : "default" }}
        >
          {file.name}
        </Text>
        <Text fz="xs" fw={300} c="dimmed">
          {file.type}
        </Text>
      </div>
    </Group>
  );
}
