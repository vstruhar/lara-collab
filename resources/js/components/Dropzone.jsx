import { Group, Image, SimpleGrid, Text, rem } from "@mantine/core";
import { Dropzone as MantineDropzone } from "@mantine/dropzone";
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
  IconFiles,
  IconUpload,
  IconX,
} from "@tabler/icons-react";
import classes from "./css/Dropzone.module.css";

export default function Dropzone({ selected, onChange, ...props }) {
  const add = (files) => {
    onChange([...selected, ...files]);
  };

  const remove = (index) => {
    const files = [...selected];
    files.splice(index, 1);
    onChange(files);
  };

  const previews = selected.map((file, index) => {
    const props = { width: rem(45), height: rem(45) };
    let icon = <IconFile style={props} />;

    if (file.type.includes("image")) {
      const imageUrl = URL.createObjectURL(file);
      icon = (
        <Image
          radius="md"
          w={45}
          h="auto"
          src={imageUrl}
          onLoad={() => URL.revokeObjectURL(imageUrl)}
        />
      );
    } else if (file.type.includes("pdf")) {
      icon = <IconFileTypePdf style={props} />;
    } else if (file.type.includes("css")) {
      icon = <IconFileTypeCss style={props} />;
    } else if (file.type.includes("csv")) {
      icon = <IconFileTypeCsv style={props} />;
    } else if (file.type.includes("docx")) {
      icon = <IconFileTypeDocx style={props} />;
    } else if (file.type.includes("doc")) {
      icon = <IconFileTypeDoc style={props} />;
    } else if (file.type.includes("html")) {
      icon = <IconFileTypeHtml style={props} />;
    } else if (file.name.includes(".jsx")) {
      icon = <IconFileTypeJsx style={props} />;
    } else if (file.type.includes("javascript")) {
      icon = <IconFileTypeJs style={props} />;
    } else if (file.type.includes("php")) {
      icon = <IconFileTypePhp style={props} />;
    } else if (file.name.includes(".sql")) {
      icon = <IconFileTypeSql style={props} />;
    } else if (file.type.includes("image/svg")) {
      icon = <IconFileTypeSvg style={props} />;
    } else if (file.type.includes("application/zip")) {
      icon = <IconFileTypeZip style={props} />;
    } else if (file.type === "text/plain") {
      icon = <IconFileTypeTxt style={props} />;
    } else if (file.name.includes(".vue")) {
      icon = <IconFileTypeVue style={props} />;
    } else if (file.type.includes("xls")) {
      icon = <IconFileTypeXls style={props} />;
    } else if (file.type.includes("xml")) {
      icon = <IconFileTypeXml style={props} />;
    } else if (file.name.includes(".ts")) {
      icon = <IconFileTypeTs style={props} />;
    }

    return (
      <Group key={index} gap="sm" wrap="nowrap" className={classes.file}>
        <div className={classes.icon}>{icon}</div>
        <div className={classes.text}>
          <Text fz={15} fw={400} truncate="end">
            {file.name}
          </Text>
          <Text fz="xs" fw={300} c="dimmed">
            {file.type}
          </Text>
        </div>
        <IconCircleX
          className={classes.remove}
          stroke={1.5}
          onClick={() => remove(index)}
        />
      </Group>
    );
  });

  return (
    <>
      <MantineDropzone
        onDrop={(files) => add(files)}
        onReject={(files) => console.log("rejected files", files)}
        {...props}
      >
        <Group
          justify="center"
          gap="md"
          mih={50}
          style={{ pointerEvents: "none" }}
        >
          <MantineDropzone.Accept>
            <IconUpload
              style={{
                width: rem(42),
                height: rem(42),
                color: "var(--mantine-color-blue-6)",
              }}
              stroke={1.5}
            />
          </MantineDropzone.Accept>
          <MantineDropzone.Reject>
            <IconX
              style={{
                width: rem(42),
                height: rem(42),
                color: "var(--mantine-color-red-6)",
              }}
              stroke={1.5}
            />
          </MantineDropzone.Reject>
          <MantineDropzone.Idle>
            <IconFiles
              style={{
                width: rem(42),
                height: rem(42),
                color: "var(--mantine-color-dimmed)",
              }}
              stroke={1.5}
            />
          </MantineDropzone.Idle>

          <div>
            <Text size="md" inline>
              Drag files here or click to select files
            </Text>
            <Text size="xs" c="dimmed" inline mt={7}>
              Files of any type will be accepted
            </Text>
          </div>
        </Group>
      </MantineDropzone>

      <SimpleGrid cols={2} mt="lg">
        {previews}
      </SimpleGrid>
    </>
  );
}
