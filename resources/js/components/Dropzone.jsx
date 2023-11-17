import { Group, SimpleGrid, Text, rem } from "@mantine/core";
import { Dropzone as MantineDropzone } from "@mantine/dropzone";
import { IconFiles, IconUpload, IconX } from "@tabler/icons-react";
import FileThumbnail from "./FileThumbnail";

export default function Dropzone({
  selected,
  onChange,
  remove,
  open,
  ...props
}) {
  return (
    <>
      <MantineDropzone
        onDrop={(files) => onChange([...selected, ...files])}
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
        {selected.map((file, index) => (
          <FileThumbnail
            key={index}
            index={index}
            file={file}
            remove={() => remove(index)}
            open={() => open(file)}
          />
        ))}
      </SimpleGrid>
    </>
  );
}
