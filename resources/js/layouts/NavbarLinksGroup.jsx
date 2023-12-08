import useNavigationStore from "@/hooks/store/useNavigationStore";
import { redirectToUrl } from "@/utils/route";
import { Box, Collapse, Group, UnstyledButton, rem } from "@mantine/core";
import { IconChevronRight } from "@tabler/icons-react";
import classes from "./css/NavbarLinksGroup.module.css";

export default function NavbarLinksGroup({ item }) {
  const { toggle, active } = useNavigationStore();
  const hasLinks = Array.isArray(item.links);

  const itemClick = () => {
    if (hasLinks) {
      toggle(item.label);
    } else {
      active(item.label, false);
      redirectToUrl(item.link);
    }
  };

  const subItemClick = (subItem) => {
    active(subItem.label, true);
    redirectToUrl(subItem.link);
  };

  return (
    <>
      <UnstyledButton
        onClick={itemClick}
        className={`${classes.control} ${item.active ? classes.active : ""}`}
      >
        <Group justify="space-between" gap={0}>
          <Box style={{ display: "flex", alignItems: "center" }}>
            <item.icon className={classes.linkIcon} stroke={1.5} />
            <Box ml="md">{item.label}</Box>
          </Box>
          {hasLinks && (
            <IconChevronRight
              className={classes.chevron}
              stroke={1.5}
              style={{
                width: rem(16),
                height: rem(16),
                transform: item.opened ? "rotate(-90deg)" : "none",
              }}
            />
          )}
        </Group>
      </UnstyledButton>
      {hasLinks ? (
        <Collapse in={item.opened}>
          {(hasLinks ? item.links.filter((l) => l.visible) : []).map((item) => (
            <UnstyledButton
              key={item.label}
              className={`${classes.link} ${item.active ? classes.active : ""}`}
              onClick={() => subItemClick(item)}
            >
              {item.label}
            </UnstyledButton>
          ))}
        </Collapse>
      ) : null}
    </>
  );
}
