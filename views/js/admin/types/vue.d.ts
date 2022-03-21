type Component = Parameters<typeof import('@vue/composition-api').defineComponent>[0];
type ComponentWithSetup<Return = Record<string, unknown>> =
  Omit<RequireProps<Component, 'setup'>, 'setup'> & { setup: ReplaceReturnType<Setup, Return> };
type Props = Component['props'];
type Model = Component['model'];
type Setup = Component['setup'];

type ComposableComponent = () => Component;
type ComposableComponentWithSetup<
  ReturnType = Record<string, unknown>,
// eslint-disable-next-line @typescript-eslint/no-explicit-any
> = (...args: any) => ComponentWithSetup<ReturnType>;

type ReplaceReturnType<T extends (...params: unknown) => unknown, Return> = (...params: Parameters<T>) => Return;

type RequireProps<T extends Record<string, unknown>, K extends keyof T> = Omit<T, K> & {
  [MK in K]-?: NonNullable<T[MK]>
};

type ValuesOf<T extends unknown[]> = T[number];
