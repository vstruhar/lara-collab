import { Group, Text, rem } from "@mantine/core";

export default function EmptyWithIcon({
  title,
  subtitle,
  icon: Icon,
  titleFontSize = 22,
  subtitleFontSize = 14,
  iconSize = 50,
  opacity = 0.6,
}) {
  return (
    <Group gap={20} opacity={opacity}>
      <Icon
        style={{
          width: rem(iconSize),
          height: rem(iconSize),
        }}
      />
      <div>
        <Text fz={titleFontSize} fw={600} lh={1.2}>
          {title}
        </Text>
        <Text fz={subtitleFontSize} opacity={0.6}>
          {subtitle}
        </Text>
      </div>
    </Group>
  );
}
