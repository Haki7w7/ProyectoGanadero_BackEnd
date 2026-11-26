import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AnimalesController } from './animales.controller';
import { AnimalesService } from './animales.service';
import { Animal } from './entities/animal.entity';

@Module({
  imports: [TypeOrmModule.forFeature([Animal])],
  controllers: [AnimalesController],
  providers: [AnimalesService],
  exports: [AnimalesService],
})
export class AnimalesModule {}