import useForm from "@/hooks/useForm";
import { hasRoles } from "@/utils/user";
import { Button, Flex, MultiSelect, Skeleton, Text } from "@mantine/core";
import { modals } from "@mantine/modals";
import axios from "axios";
import { useEffect, useState } from "react";

function ModalForm({ item }) {
  const [requestPending, setRequestPending] = useState(true);
  const [users, setUsers] = useState([]);
  const [clients, setClients] = useState([]);

  const [form, submit, updateValue] = useForm(
    "post",
    route("projects.user_access", item.id),
    {
      users: item.users_with_access
        .filter((user) => !hasRoles(user, ["admin", "client"]))
        .map((i) => i.id.toString()),
      clients: item.users_with_access
        .filter(
          (user) =>
            hasRoles(user, ["client"]) && user.reason !== "company owner",
        )
        .map((i) => i.id.toString()),
    },
  );

  const submitModal = (event) => {
    submit(event, {
      onSuccess: () => modals.closeAll(),
      preserveScroll: true,
    });
  };

  useEffect(() => {
    axios
      .get(route("dropdown.values", ["users", "clients"]))
      .then(({ data }) => {
        setUsers([...data.users]);
        setClients([...data.clients]);
      })
      .catch(() =>
        alert("Something went wrong, failed to load dropdown values"),
      )
      .finally(() => setRequestPending(false));
  }, [form.data]);

  return (
    <form onSubmit={submitModal}>
      {requestPending ? (
        <>
          <Skeleton height={10} width={50} mt={8} radius="xl" />
          <Skeleton height={25} mt={10} radius="xl" />

          <Skeleton height={10} width={50} mt={25} radius="xl" />
          <Skeleton height={25} mt={10} radius="xl" />
        </>
      ) : (
        <>
          <MultiSelect
            label="Users"
            placeholder="Select users"
            searchable
            value={requestPending ? [] : form.data.users}
            onChange={(values) => updateValue("users", values)}
            data={users}
            error={form.errors.users}
          />

          <MultiSelect
            label="Clients"
            placeholder="Select clients"
            searchable
            mt="md"
            value={requestPending ? [] : form.data.clients}
            onChange={(values) => updateValue("clients", values)}
            data={clients}
            error={form.errors.clients}
          />
        </>
      )}

      <Flex justify="flex-end" mt="xl">
        <Button
          type="submit"
          w={100}
          disabled={requestPending}
          loading={form.processing}
        >
          Save
        </Button>
      </Flex>
    </form>
  );
}

const UserAccessModal = (item) => {
  modals.open({
    title: (
      <Text size="xl" fw={700} mb={-10}>
        User access
      </Text>
    ),
    centered: true,
    padding: "xl",
    overlayProps: { backgroundOpacity: 0.55, blur: 3 },
    children: <ModalForm item={item} />,
  });
};

export default UserAccessModal;
