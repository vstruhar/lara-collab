import { useState } from "react";
import { router } from "@inertiajs/react";
import { Group, Box, Collapse, Text, UnstyledButton, rem } from "@mantine/core";
import { IconChevronRight } from "@tabler/icons-react";
import classes from "./css/NavbarLinksGroup.module.css";

export default function NavbarLinksGroup({
  icon: Icon,
  label,
  active,
  link,
  links,
}) {
  const hasLinks = Array.isArray(links);
  const [opened, setOpened] = useState(active || false);

  const items = (hasLinks ? links : []).map((item) => (
    <Text
      component="a"
      className={`${classes.link} ${item.active ? classes.active : ""}`}
      href={item.link}
      key={item.label}
      onClick={() => router.get(item.link)}
    >
      {item.label}
    </Text>
  ));

  const handleClick = () => {
    if (hasLinks) {
      setOpened((o) => !o);
    } else {
      router.get(link);
    }
  };

  return (
    <>
      <UnstyledButton
        onClick={handleClick}
        className={`${classes.control} ${active ? classes.active : ""}`}
      >
        <Group justify="space-between" gap={0}>
          <Box style={{ display: "flex", alignItems: "center" }}>
            <Icon className={classes.linkIcon} stroke={1.5} />
            <Box ml="md">{label}</Box>
          </Box>
          {hasLinks && (
            <IconChevronRight
              className={classes.chevron}
              stroke={1.5}
              style={{
                width: rem(16),
                height: rem(16),
                transform: opened ? "rotate(-90deg)" : "none",
              }}
            />
          )}
        </Group>
      </UnstyledButton>
      {hasLinks ? <Collapse in={opened}>{items}</Collapse> : null}
    </>
  );
}
