import { dispatchEvents } from '../../Support/DispatchEvents';
import {beforeEach, describe, expect, it, jest} from '@jest/globals'

describe('dispatchEvents', () => {
  let component;

  beforeEach(() => {
    component = {
      $el: {
        closest: jest.fn().mockReturnValue({
          dataset: {
            rowKey: '123',
          },
        }),
      },
      $dispatch: jest.fn(),
    };
  });

  it('should return early if events is not provided', () => {
    dispatchEvents(null, 'eventType', component);
    expect(component.$dispatch).not.toHaveBeenCalled();
  });

  it('should return early if events is not a string', () => {
    dispatchEvents([], 'eventType', component);
    expect(component.$dispatch).not.toHaveBeenCalled();
  });

  it('should replace {row-id} with rowKey from dataset', () => {
    dispatchEvents('{row-id}', 'eventType', component);
    expect(component.$dispatch).toHaveBeenCalledWith('123', {});
  });

  it('should dispatch events correctly', () => {
    const events = 'click|param1=value1;param2=value2,hover|param3=value3';
    dispatchEvents(events, 'eventType', component, { extraParam: 'extraValue' });

    expect(component.$dispatch).toHaveBeenCalledTimes(2);
    expect(component.$dispatch).toHaveBeenNthCalledWith(1, 'click', {
      extraParam: 'extraValue',
      param1: 'value1',
      param2: 'value2',
    });
    expect(component.$dispatch).toHaveBeenNthCalledWith(2,'hover', {
      extraParam: 'extraValue',
      param3: 'value3',
    });
  });

  it('should not dispatch events if type is error', () => {
    dispatchEvents('click', 'error', component);
    expect(component.$dispatch).not.toHaveBeenCalled();
  });
});
